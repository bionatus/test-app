<?php

namespace Tests\Feature\LiveApi\V2\Order\Cancel;

use App;
use App\Constants\PubnubMessageTypes;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Events\Order\Declined;
use App\Http\Controllers\LiveApi\V2\Order\CancelController;
use App\Http\Resources\LiveApi\V2\Order\DetailedResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderStaff;
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

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_CANCEL_STORE;

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

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_cancels_an_order(int $substatusId)
    {
        Event::fake(Declined::class);

        $supplier = Supplier::factory()->withEmail()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        $staff    = Staff::factory()->usingSupplier($supplier)->create(['name' => 'fake Name']);
        OrderStaff::factory()->usingStaff($staff)->usingOrder($order)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        OrderDelivery::factory()->usingOrder($order)->create();

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema()), $response);

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'detail'       => null,
            'substatus_id' => Substatus::STATUS_CANCELED_DECLINED,
            'order_id'     => $order->getKey(),
        ]);
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED],
            [Substatus::STATUS_PENDING_ASSIGNED],
        ];
    }

    /** @test */
    public function it_dispatches_a_canceled_by_supplier_event()
    {
        Event::fake(Declined::class);

        $supplier = Supplier::factory()->withEmail()->createQuietly();
        $order    = Order::factory()->pending()->usingSupplier($supplier)->create();

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());

        $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        Event::assertDispatched(Declined::class);
    }

    /** @test */
    public function it_publishes_a_message_in_the_pubnub_channel()
    {
        Event::fake(Declined::class);

        $supplier = Supplier::factory()->withEmail()->createQuietly();
        $order    = Order::factory()->pending()->usingSupplier($supplier)->createQuietly();

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->withAnyArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with(PubnubMessageTypes::ORDER_DECLINED)->once()->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->once()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->once()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());

        $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));
    }
}
