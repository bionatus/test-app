<?php

namespace Tests\Feature\LiveApi\V1\Order\InProgress\Delivery;

use App;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\DeliveryEtaUpdated;
use App\Http\Controllers\LiveApi\V1\Order\InProgress\DeliveryController;
use App\Http\Resources\LiveApi\V1\Order\OrderDeliveryResource;
use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use App\Models\ShipmentDelivery;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\WarehouseDelivery;
use App\Services\Curri\Curri;
use Auth;
use DB;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see DeliveryController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_DELIVERY_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $supplier = Supplier::factory()->createQuietly();
        $route    = URL::route($this->routeName, Order::factory()->usingSupplier($supplier)->create());

        $this->patch($route);
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:updateInProgressDelivery,' . RouteParameters::ORDER]);
    }

    /** @test
     * @dataProvider typeProvider
     */
    public function it_updates_the_order_delivery_in_progress(string $type, ?bool $useStoreAddress)
    {
        Event::fake(DeliveryEtaUpdated::class);

        Carbon::setTestNow('2022-10-11 05:00:00AM');

        $supplier = Supplier::factory()->createQuietly([
            'address'   => 'supplier address',
            'address_2' => 'supplier address 2',
            'city'      => 'supplier city',
            'state'     => 'supplier state',
            'zip_code'  => 'supplier zip code',
            'country'   => 'supplier country',
        ]);

        $staff         = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create([
            'type'       => $type,
            'date'       => Carbon::now()->addDay()->format('Y-m-d'),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);

        if ($type === OrderDelivery::TYPE_CURRI_DELIVERY) {
            CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();
        }

        if ($type === OrderDelivery::TYPE_WAREHOUSE_DELIVERY) {
            WarehouseDelivery::factory()->usingOrderDelivery($orderDelivery)->create();
        }
        if ($type === OrderDelivery::TYPE_PICKUP) {
            Pickup::factory()->usingOrderDelivery($orderDelivery)->create();
        }
        if ($type === OrderDelivery::TYPE_SHIPMENT_DELIVERY) {
            ShipmentDelivery::factory()->usingOrderDelivery($orderDelivery)->create();
        }
        $startTime = Carbon::createFromTime(6);
        $endTime   = Carbon::createFromTime(9);
        $data      = [
            RequestKeys::DATE       => $date = Carbon::now()->format('Y-m-d'),
            RequestKeys::START_TIME => $startTime->format('H:i'),
            RequestKeys::END_TIME   => $endTime->format('H:i'),
        ];

        if ($type !== OrderDelivery::TYPE_PICKUP && $type !== OrderDelivery::TYPE_CURRI_DELIVERY) {
            $data[RequestKeys::FEE] = 123456;
        }

        if (!is_null($useStoreAddress)) {
            $data[RequestKeys::USE_STORE_ADDRESS] = $useStoreAddress;
        }

        if ($type === OrderDelivery::TYPE_CURRI_DELIVERY && !$useStoreAddress) {
            $data[RequestKeys::ADDRESS]   = 'address';
            $data[RequestKeys::ADDRESS_2] = 'address 2';
            $data[RequestKeys::CITY]      = 'city';
            $data[RequestKeys::STATE]     = 'state';
            $data[RequestKeys::ZIP_CODE]  = '12345';
            $data[RequestKeys::COUNTRY]   = 'country';
        }

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('getQuote')
            ->withAnyArgs()
            ->times($type == OrderDelivery::TYPE_CURRI_DELIVERY ? 1 : 0)
            ->andReturn([
                'fee'     => 1200,
                'quoteId' => 'abc-123',
            ]);
        App::bind(Curri::class, fn() => $curri);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->patch(URL::route($this->routeName, $order), $data);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(OrderDeliveryResource::jsonSchema()), $response);

        $dbFormat = 'H:i:s';
        if (DB::connection()->getName() == 'sqlite') {
            $dbFormat = 'H:i';
        }

        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'id'         => $orderDelivery->getKey(),
            'order_id'   => $order->getKey(),
            'type'       => $type,
            'date'       => $date,
            'start_time' => $startTime->format($dbFormat),
            'end_time'   => $endTime->format($dbFormat),
        ]);
    }

    public function typeProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, true],
            [OrderDelivery::TYPE_CURRI_DELIVERY, false],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, null],
            [OrderDelivery::TYPE_PICKUP, null],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, null],
        ];
    }

    /**
     * @test
     * @dataProvider addressProvider
     */
    public function it_updates_the_order_delivery_origin_address_based_on_use_supplier_address_parameter(
        bool $useStoreAddress
    ) {
        Event::fake(DeliveryEtaUpdated::class);

        Carbon::setTestNow('2022-10-11 05:00:00AM');

        $supplier      = Supplier::factory()->createQuietly([
            'address'   => $supplierAddress = 'supplier address',
            'address_2' => $supplierAddress2 = 'supplier address 2',
            'city'      => $supplierCity = 'supplier city',
            'state'     => $supplierState = 'supplier state',
            'zip_code'  => $supplierZip = '11111',
            'country'   => $supplierCountry = 'supplier country',
        ]);
        $staff         = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create([
            'date'       => Carbon::now()->addDay()->format('Y-m-d'),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('getQuote')->withAnyArgs()->once()->andReturn([
            'fee'     => 1200,
            'quoteId' => $quoteId = 'abc-123',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $route = URL::route($this->routeName, $order);

        Auth::shouldUse('live');
        $this->login($staff);

        $data = [
            RequestKeys::DATE              => Carbon::now()->format('Y-m-d'),
            RequestKeys::START_TIME        => Carbon::createFromTime(6)->format('H:i'),
            RequestKeys::END_TIME          => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::USE_STORE_ADDRESS => $useStoreAddress,
        ];

        if (!$useStoreAddress) {
            $data[RequestKeys::ADDRESS]   = $address = 'address';
            $data[RequestKeys::ADDRESS_2] = $address2 = 'address 2';
            $data[RequestKeys::CITY]      = $city = 'city';
            $data[RequestKeys::STATE]     = $state = 'state';
            $data[RequestKeys::ZIP_CODE]  = $zip = '12345';
            $data[RequestKeys::COUNTRY]   = $country = 'country';
        }

        $response = $this->patch($route, $data);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(OrderDeliveryResource::jsonSchema()), $response);
        $this->assertSame(12, json_decode($response->getContent())->data->fee);
        $this->assertSame($quoteId, json_decode($response->getContent())->data->info->quote_id);
        $this->assertDatabaseHas(Address::tableName(), [
            'address_1' => ($useStoreAddress) ? $supplierAddress : $address,
            'address_2' => ($useStoreAddress) ? $supplierAddress2 : $address2,
            'city'      => ($useStoreAddress) ? $supplierCity : $city,
            'state'     => ($useStoreAddress) ? $supplierState : $state,
            'zip_code'  => ($useStoreAddress) ? $supplierZip : $zip,
            'country'   => ($useStoreAddress) ? $supplierCountry : $country,
        ]);
    }

    public function addressProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /** @test */
    public function it_dispatches_delivery_eta_updated_event_if_eta_is_updated()
    {
        Event::fake(DeliveryEtaUpdated::class);

        Carbon::setTestNow('2022-10-11 05:00:00AM');

        $supplier      = Supplier::factory()->createQuietly([
            'address'   => 'supplier address',
            'address_2' => 'supplier address 2',
            'city'      => 'supplier city',
            'state'     => 'supplier state',
            'zip_code'  => '11111',
            'country'   => 'supplier country',
        ]);
        $staff         = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create([
            'date'       => Carbon::now()->addDay()->format('Y-m-d'),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('getQuote')->withAnyArgs()->once()->andReturn([
            'fee'     => 1200,
            'quoteId' => 'abc-123',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $route = URL::route($this->routeName, $order);

        Auth::shouldUse('live');
        $this->login($staff);

        $data = [
            RequestKeys::DATE              => Carbon::now()->format('Y-m-d'),
            RequestKeys::START_TIME        => Carbon::createFromTime(6)->format('H:i'),
            RequestKeys::END_TIME          => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::USE_STORE_ADDRESS => true,
        ];

        $response = $this->patch($route, $data);

        $response->assertStatus(Response::HTTP_OK);

        Event::assertDispatched(DeliveryEtaUpdated::class);
    }

    /** @test */
    public function it_does_not_dispatch_delivery_eta_updated_event_if_eta_is_not_updated()
    {
        Event::fake(DeliveryEtaUpdated::class);

        Carbon::setTestNow('2022-10-11 05:00:00AM');

        $supplier      = Supplier::factory()->createQuietly([
            'address'   => 'supplier address',
            'address_2' => 'supplier address 2',
            'city'      => 'supplier city',
            'state'     => 'supplier state',
            'zip_code'  => '11111',
            'country'   => 'supplier country',
        ]);
        $staff         = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create([
            'date'       => $date = Carbon::now()->format('Y-m-d'),
            'start_time' => $startTime = Carbon::createFromTime(6)->format('H:i'),
            'end_time'   => $endTime = Carbon::createFromTime(9)->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('getQuote')->withAnyArgs()->once()->andReturn([
            'fee'     => 1200,
            'quoteId' => $quoteId = 'abc-123',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $route = URL::route($this->routeName, $order);

        Auth::shouldUse('live');
        $this->login($staff);

        $data = [
            RequestKeys::DATE              => $date,
            RequestKeys::START_TIME        => $startTime,
            RequestKeys::END_TIME          => $endTime,
            RequestKeys::USE_STORE_ADDRESS => true,
        ];

        $response = $this->patch($route, $data);

        $response->assertStatus(Response::HTTP_OK);

        Event::assertNotDispatched(DeliveryEtaUpdated::class);
    }
}
