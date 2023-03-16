<?php

namespace Tests\Feature\Api\V4\Order\Approve;

use App;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\Approved;
use App\Events\Order\Delivery\Curri\Booked;
use App\Http\Controllers\Api\V4\Order\ApproveController;
use App\Http\Requests\Api\V4\Order\Approve\InvokeRequest;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Jobs\Order\Delivery\Curri\DelayBooking;
use App\Jobs\Order\Delivery\Pickup\DelayApprovedReadyForDelivery;
use App\Jobs\Order\SetTotalOrdersInformationNewStatuses as SetTotalOrdersInformationNewStatusesJob;
use App\Models\CurriDelivery;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Pickup;
use App\Models\ShipmentDelivery;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Curri\Curri;
use Bus;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ApproveController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V4_ORDER_APPROVE_STORE;

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
        $this->assertRouteUsesMiddleware($this->routeName, ['can:approve,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_returns_correct_resource()
    {
        Event::fake(Approved::class);

        $tomorrow = Carbon::tomorrow();
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pendingApproval()->usingUser($user)->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);

        OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => $tomorrow->format('Y-m-d'),
            'start_time' => $tomorrow->format('h:m:i'),
        ]);

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($order->getRouteKey(), $data['id']);

        $this->assertDatabaseHas(Order::tableName(), [
            'id'   => $order->getKey(),
            'name' => null,
        ]);

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
        ]);
    }

    /** @test */
    public function it_returns_correct_resource_with_correct_status()
    {
        Event::fake(Approved::class);

        $tomorrow = Carbon::tomorrow();
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pendingApproval()->usingUser($user)->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);

        OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => $tomorrow->format('Y-m-d'),
            'start_time' => $tomorrow->format('h:m:i'),
        ]);

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($order->getRouteKey(), $data['id']);

        $this->assertDatabaseHas(Order::tableName(), [
            'id'   => $order->getKey(),
            'name' => null,
        ]);

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
        ]);
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_approves_an_order_with_his_order_sub_status_depending_of_order_delivery_type_and_if_has_pending_items(
        string $typeDelivery,
        bool $needItNow,
        bool $hasPendingItems,
        int $subStatusExpectedId
    ) {
        Event::fake(Approved::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pendingApproval()->usingUser($user)->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);

        ItemOrder::factory()->usingOrder($order)->available()->create();

        if ($hasPendingItems) {
            ItemOrder::factory()->usingOrder($order)->pending()->create();
        }

        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create([
            'type'          => $typeDelivery,
            'is_needed_now' => $needItNow,
            'date'          => Carbon::now()->addDay(),
            'start_time'    => Carbon::createFromTime(9)->format('H:i'),
            'end_time'      => Carbon::createFromTime(12)->format('H:i'),
        ]);

        if ($typeDelivery === OrderDelivery::TYPE_CURRI_DELIVERY) {
            $curri = Mockery::mock(Curri::class);
            if ($subStatusExpectedId == 310) {
                $curri->shouldReceive('bookDelivery')->withAnyArgs()->once()->andReturn([
                    'id'          => 'book id',
                    'price'       => 1200,
                    'tracking_id' => 'tracking id',
                ]);
            }
            App::bind(Curri::class, fn() => $curri);
            CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();
        }

        if ($typeDelivery === OrderDelivery::TYPE_PICKUP) {
            Pickup::factory()->usingOrderDelivery($orderDelivery)->create();
        }

        if ($typeDelivery === OrderDelivery::TYPE_SHIPMENT_DELIVERY) {
            ShipmentDelivery::factory()->usingOrderDelivery($orderDelivery)->create();
        }

        $expectedName = 'Fake order name';

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]),
            [RequestKeys::NAME => $expectedName]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($order->getRouteKey(), $data['id']);

        $this->assertDatabaseHas(Order::tableName(), [
            'id'   => $order->getKey(),
            'name' => $expectedName,
        ]);

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => $subStatusExpectedId,
        ]);
    }

    public function dataProvider(): array
    {
        return [
            //OrderDelivery Type , Need It Now, has Pending Items , Expected id SubStatus
            ['pickup', false, true, 210],
            ['pickup', false, false, 300],
            ['pickup', true, true, 210],
            ['pickup', true, false, 310],
            ['curri_delivery', false, true, 210],
            ['curri_delivery', false, false, 300],
            ['curri_delivery', true, true, 210],
            ['curri_delivery', true, false, 310],
            ['shipment_delivery', false, true, 210],
            ['shipment_delivery', false, false, 210],
        ];
    }

    /** @test */
    public function it_dispatches_a_curri_booked_event_when_conditions_satisfy()
    {
        Event::fake([Approved::class, Booked::class]);

        $supplier = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();

        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'          => Carbon::now(),
            'start_time'    => Carbon::createFromTime(15)->format('H:i'),
            'end_time'      => Carbon::createFromTime(18)->format('H:i'),
            'fee'           => 1000,
            'is_needed_now' => true,
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();
        $curri = Mockery::mock(Curri::class);

        $curri->shouldReceive('bookDelivery')->withAnyArgs()->once()->andReturn([
            'id'          => 'book id',
            'price'       => 1200,
            'tracking_id' => 'tracking id',
        ]);

        App::bind(Curri::class, fn() => $curri);
        $this->login($order->user);

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched(Booked::class);
    }

    /** @test */
    public function it_dispatch_the_delay_booking_job_when_requirements_satisfy()
    {
        Bus::fake();

        Carbon::setTestNow('2022-11-10 04:12AM');

        $supplier = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();

        OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'          => Carbon::now(),
            'start_time'    => Carbon::createFromTime(15)->format('H:i'),
            'end_time'      => Carbon::createFromTime(18)->format('H:i'),
            'fee'           => 1000,
            'is_needed_now' => false,
        ]);

        $this->login($order->user);

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        Bus::assertDispatched(DelayBooking::class);
    }

    /** @test */
    public function it_dispatch_the_delay_approved_ready_for_delivery_job_when_requirements_satisfy()
    {
        Bus::fake();

        Carbon::setTestNow('2022-11-10 04:12AM');

        $supplier = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();

        OrderDelivery::factory()->usingOrder($order)->pickup()->create([
            'date'          => Carbon::now(),
            'start_time'    => Carbon::createFromTime(15)->format('H:i'),
            'end_time'      => Carbon::createFromTime(18)->format('H:i'),
            'fee'           => 1000,
            'is_needed_now' => false,
        ]);

        $this->login($order->user);

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        Bus::assertDispatched(DelayApprovedReadyForDelivery::class);
    }

    /** @test */
    public function it_does_not_dispatch_an_approved_event_if_delivery_is_shipment()
    {
        Event::fake([Approved::class]);

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();
        OrderDelivery::factory()->usingOrder($order)->shipmentDelivery()->create();

        $this->login($order->user);

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertNotDispatched(Approved::class);
    }

    /** @test */
    public function it_does_not_dispatch_an_approved_event_if_user_has_pending_items()
    {
        Event::fake([Approved::class]);

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();
        OrderDelivery::factory()->usingOrder($order)->pickup()->create();
        ItemOrder::factory()->usingOrder($order)->available()->create();
        ItemOrder::factory()->usingOrder($order)->pending()->create();

        $this->login($order->user);

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertNotDispatched(Approved::class);
    }

    /** @test */
    public function it_dispatches_a_curri_booked_event_when_start_time_is_minor_to_30_minutes()
    {
        Event::fake([Approved::class, Booked::class]);

        $supplier = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();
        $now      = Carbon::now();

        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'          => $now->format('Y-m-d'),
            'start_time'    => $now->addMinutes(15)->format('H:i'),
            'is_needed_now' => false,
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();
        $curri = Mockery::mock(Curri::class);

        $curri->shouldReceive('bookDelivery')->withAnyArgs()->once()->andReturn([
            'id'          => 'book id',
            'price'       => 1200,
            'tracking_id' => 'tracking id',
        ]);

        App::bind(Curri::class, fn() => $curri);
        $this->login($order->user);

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched(Booked::class);
    }

    /** @test */
    public function it_changes_an_approved_order_pickup_when_start_time_is_minor_to_30_minutes()
    {
        Event::fake([Approved::class]);

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();
        $now      = Carbon::now();

        OrderDelivery::factory()->usingOrder($order)->pickup()->create([
            'date'          => $now->format('Y-m-d'),
            'start_time'    => $now->addMinutes(15)->format('H:i'),
            'is_needed_now' => false,
        ]);
        ItemOrder::factory()->usingOrder($order)->available()->create();
        ItemOrder::factory()->usingOrder($order)->available()->create();

        $this->login($order->user);

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
        ]);
    }

    /** @test */
    public function it_dispatches_an_order_substatus_created_event_when_order_has_pending_items_or_shipment_delivery()
    {
        Event::fake([Approved::class, 'eloquent.created: ' . OrderSubstatus::class]);
        Bus::fake([SetTotalOrdersInformationNewStatusesJob::class]);

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();
        OrderDelivery::factory()->usingOrder($order)->pickup()->create();
        ItemOrder::factory()->usingOrder($order)->available()->create();
        ItemOrder::factory()->usingOrder($order)->pending()->create();

        $this->login($order->user);

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched('eloquent.created: ' . OrderSubstatus::class);
    }

    /** @test */
    public function it_dispatches_an_order_substatus_created_event_when_order_has_no_pending_for_curri_delivery()
    {
        Event::fake([Approved::class, Booked::class, 'eloquent.created: ' . OrderSubstatus::class]);

        $supplier = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();

        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'          => Carbon::now(),
            'start_time'    => Carbon::createFromTime(15)->format('H:i'),
            'end_time'      => Carbon::createFromTime(18)->format('H:i'),
            'fee'           => 1000,
            'is_needed_now' => true,
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('bookDelivery')->withAnyArgs()->once()->andReturn([
            'id'          => 'book id',
            'price'       => 1200,
            'tracking_id' => 'tracking id',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $this->login($order->user);

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched('eloquent.created: ' . OrderSubstatus::class);
    }

    /** @test */
    public function it_dispatches_an_order_substatus_created_event_when_order_has_no_pending_for_pickup_delivery()
    {
        Event::fake([Approved::class, 'eloquent.created: ' . OrderSubstatus::class]);

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();
        $now      = Carbon::now();

        OrderDelivery::factory()->usingOrder($order)->pickup()->create([
            'date'          => $now->format('Y-m-d'),
            'start_time'    => $now->addMinutes(15)->format('H:i'),
            'is_needed_now' => false,
        ]);
        ItemOrder::factory()->usingOrder($order)->available()->create();

        $this->login($order->user);

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));
        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched('eloquent.created: ' . OrderSubstatus::class);
    }
}
