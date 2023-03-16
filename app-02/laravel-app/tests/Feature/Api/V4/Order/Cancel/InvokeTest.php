<?php

namespace Tests\Feature\Api\V4\Order\Cancel;

use App;
use App\Constants\PubnubMessageTypes;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\CanceledByUser;
use App\Http\Controllers\Api\V4\Order\CancelController;
use App\Http\Requests\Api\V4\Order\Cancel\StoreRequest;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\ItemOrder;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery;
use PubNub\PubNub;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see CancelController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V4_ORDER_CANCEL_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName,
            [RouteParameters::ORDER => Order::factory()->createQuietly()->getRouteKey()]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:cancel,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_cancels_an_order_in_pending_approval_status()
    {
        Event::fake(CanceledByUser::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()
            ->usingSubstatus(Substatus::find(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED))
            ->usingUser($user)
            ->usingSupplier($supplier)
            ->create([
                'working_on_it' => 'John Doe',
            ]);
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });
        ItemOrder::factory()->usingOrder($order)->create();

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]);

        $this->login($user);
        $response = $this->post($route, [RequestKeys::STATUS_DETAIL => $detail = 'Took too long']);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(Order::tableName(), [
            'id' => $order->getKey(),
        ]);

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'detail'       => $detail,
            'substatus_id' => Substatus::STATUS_CANCELED_REJECTED,
            'order_id'     => $order->getKey(),
        ]);
    }

    /** @test */
    public function it_cancels_an_order_in_pending_status()
    {
        Event::fake(CanceledByUser::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pending()->usingUser($user)->usingSupplier($supplier)->create();
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });
        ItemOrder::factory()->usingOrder($order)->create();

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]);

        $this->login($user);
        $response = $this->post($route, [RequestKeys::STATUS_DETAIL => $detail = 'Customer Rejected Job']);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(Order::tableName(), [
            'id' => $order->getKey(),
        ]);

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'detail'       => $detail,
            'substatus_id' => Substatus::STATUS_CANCELED_ABORTED,
            'order_id'     => $order->getKey(),
        ]);
    }

    /** @test */
    public function it_publishes_a_message_in_the_pubnub_channel()
    {
        Event::fake(CanceledByUser::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pending()->usingUser($user)->usingSupplier($supplier)->create();

        ItemOrder::factory()->usingOrder($order)->create();

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->withAnyArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with(PubnubMessageTypes::ORDER_CANCELED)->once()->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]);

        $this->login($user);
        $this->post($route, [RequestKeys::STATUS_DETAIL => 'Unacceptable Quote']);
    }

    /** @test */
    public function it_publishes_a_message_in_the_pubnub_channel_with_bid_number_when_the_order_had_it()
    {
        Event::fake(CanceledByUser::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pending()->usingUser($user)->usingSupplier($supplier)->create([
            'bid_number' => $bidNumber = 'bid_number',
        ]);

        ItemOrder::factory()->usingOrder($order)->create();

        $messageExpected = [
            'text' => 'Quote Cancelled',
            'type' => 'order_canceled',
        ];;
        $messageExpected['bid_number'] = $bidNumber;

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->withAnyArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with($messageExpected)->once()->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]);

        $this->login($user);
        $this->post($route, [RequestKeys::STATUS_DETAIL => 'Unacceptable Quote']);
    }

    /** @test */
    public function it_dispatches_a_canceled_by_user_event()
    {
        Event::fake(CanceledByUser::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pending()->usingUser($user)->usingSupplier($supplier)->create();

        ItemOrder::factory()->usingOrder($order)->create();

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]);

        $this->login($user);
        $this->post($route, [RequestKeys::STATUS_DETAIL => 'Unacceptable Quote']);

        Event::assertDispatched(CanceledByUser::class);
    }

    /** @test */
    public function it_stores_custom_detail_if_order_has_no_available_item_orders()
    {
        Event::fake(CanceledByUser::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pending()->usingUser($user)->usingSupplier($supplier)->create();
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });
        ItemOrder::factory()->usingOrder($order)->notAvailable()->create();
        ItemOrder::factory()->usingOrder($order)->removed()->create();

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]);

        $this->login($user);
        $response = $this->post($route, [RequestKeys::STATUS_DETAIL => 'Customer message']);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(Order::tableName(), [
            'id' => $order->getKey(),
        ]);

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'detail'       => 'Supplier didn\'t have parts',
            'substatus_id' => Substatus::STATUS_CANCELED_ABORTED,
            'order_id'     => $order->getKey(),
        ]);
    }
}
