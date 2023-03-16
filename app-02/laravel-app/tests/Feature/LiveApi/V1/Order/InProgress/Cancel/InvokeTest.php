<?php

namespace Tests\Feature\LiveApi\V1\Order\InProgress\Cancel;

use App;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\Canceled;
use App\Http\Controllers\LiveApi\V1\Order\InProgress\CancelController;
use App\Http\Resources\LiveApi\V1\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use App\Models\Supplier;
use Auth;
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

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_CANCEL_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);
        $supplier = Supplier::factory()->createQuietly();
        $this->post(URL::route($this->routeName,
            [RouteParameters::ORDER => Order::factory()->usingSupplier($supplier)->create()->getRouteKey()]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:cancelInProgress,' . RouteParameters::ORDER]);
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_cancels_an_order(int $substatusId)
    {
        Event::fake(Canceled::class);

        $supplier = Supplier::factory()->withEmail()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();

        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'detail'       => null,
            'substatus_id' => Substatus::STATUS_CANCELED_CANCELED,
            'order_id'     => $order->getKey(),
        ]);
    }

    /** @test */
    public function it_publishes_a_message_in_the_pubnub_channel()
    {
        Event::fake(Canceled::class);

        $supplier = Supplier::factory()->withEmail()->createQuietly();
        $order    = Order::factory()->completed()->usingSupplier($supplier)->create();
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $expectedPubnubChannelMessage = [
            'text' => 'Order Cancelled',
            'type' => 'order_declined',
        ];

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->withAnyArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with($expectedPubnubChannelMessage)->once()->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));
        $response->assertStatus(Response::HTTP_CREATED);
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY],
            [Substatus::STATUS_COMPLETED_DONE],
        ];
    }

    /** @test */
    public function it_dispatches_a_canceled_by_supplier_event_and_a_status_changed_event()
    {
        Event::fake([Canceled::class]);

        $supplier = Supplier::factory()->withEmail()->createQuietly();
        $order    = Order::factory()->approved()->usingSupplier($supplier)->create();
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));
        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched(Canceled::class);
    }
}
