<?php

namespace Tests\Feature\LiveApi\V1\Order\Delivery;

use App;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Order\DeliveryController;
use App\Http\Resources\LiveApi\V1\Order\OrderDeliveryResource;
use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OtherDelivery;
use App\Models\ShipmentDelivery;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\WarehouseDelivery;
use App\Services\Curri\Curri;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_DELIVERY_UPDATE;

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
        $this->assertRouteUsesMiddleware($this->routeName, ['can:update,' . RouteParameters::ORDER]);
    }

    /** @test
     * @dataProvider typeProvider
     */
    public function it_updates_the_order_delivery(string $type, ?bool $useStoreAddress)
    {
        $supplier      = Supplier::factory()->createQuietly([
            'address'   => 'supplier address',
            'address_2' => 'supplier address 2',
            'city'      => 'supplier city',
            'state'     => 'supplier state',
            'zip_code'  => 'supplier zip code',
            'country'   => 'supplier country',
        ]);
        $staff         = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()
            ->usingSupplier($supplier)
            ->pending()
            ->create(['working_on_it' => 'Jon Doe']);
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $data = [
            RequestKeys::TYPE => $type,
        ];

        if ($type === OrderDelivery::TYPE_CURRI_DELIVERY) {
            $data[RequestKeys::VEHICLE_TYPE] = CurriDelivery::VEHICLE_TYPE_CAR;
        }

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
        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'id'       => $orderDelivery->getKey(),
            'order_id' => $order->getKey(),
            'type'     => $type,
        ]);
    }

    /** @test
     * @dataProvider addressProvider
     */
    public function it_updates_the_order_delivery_origin_address_based_on_use_supplier_address_parameter(
        bool $useStoreAddress
    ) {
        $supplier      = Supplier::factory()->createQuietly([
            'address'   => $supplierAddress = 'supplier address',
            'address_2' => $supplierAddress2 = 'supplier address 2',
            'city'      => $supplierCity = 'supplier city',
            'state'     => $supplierState = 'supplier state',
            'zip_code'  => $supplierZip = '11112',
            'country'   => $supplierCountry = 'supplier country',
        ]);
        $staff         = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()
            ->usingSupplier($supplier)
            ->pending()
            ->create(['working_on_it' => 'Jon Doe']);
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->shipmentDelivery()->create();
        ShipmentDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

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
            RequestKeys::TYPE              => OrderDelivery::TYPE_CURRI_DELIVERY,
            RequestKeys::VEHICLE_TYPE      => CurriDelivery::VEHICLE_TYPE_CAR,
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

    public function typeProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, true],
            [OrderDelivery::TYPE_CURRI_DELIVERY, false],
            [OrderDelivery::TYPE_OTHER_DELIVERY, null],
            [OrderDelivery::TYPE_PICKUP, null],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, null],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, null],
        ];
    }

    /** @test
     * @dataProvider typeWithDestinationProvider
     */
    public function it_preserves_destination_address_when_corresponds(string $type)
    {
        $supplier      = Supplier::factory()->createQuietly([
            'address'   => 'supplier address',
            'address_2' => 'supplier address 2',
            'city'      => 'supplier city',
            'state'     => 'supplier state',
            'zip_code'  => 'supplier zip code',
            'country'   => 'supplier country',
        ]);
        $staff         = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->pending()->create(['working_on_it' => 'john doe']);
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->warehouseDelivery()->create();
        $address       = Address::factory()->create();
        WarehouseDelivery::factory()->usingOrderDelivery($orderDelivery)->usingDestinationAddress($address)->create();

        $data = [
            RequestKeys::TYPE => $type,
        ];

        if ($type === OrderDelivery::TYPE_CURRI_DELIVERY) {
            $data[RequestKeys::VEHICLE_TYPE]      = CurriDelivery::VEHICLE_TYPE_CAR;
            $data[RequestKeys::USE_STORE_ADDRESS] = 1;
        }

        if ($type !== OrderDelivery::TYPE_CURRI_DELIVERY) {
            $data[RequestKeys::FEE] = 123456;
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

        $newAddress      = $response->json('data.info.destination_address');
        $expectedAddress = [
            'address_1' => $address->address_1,
            'address_2' => $address->address_2,
            'city'      => $address->city,
            'state'     => $address->state,
            'country'   => $address->country,
            'zip_code'  => $address->zip_code,
            'latitude'  => $address->latitude,
            'longitude' => $address->longitude,
        ];
        $this->assertEquals($expectedAddress, $newAddress);

        $orderDelivery->refresh();

        /** @var CurriDelivery|OtherDelivery|ShipmentDelivery|WarehouseDelivery $deliverable */
        $deliverable = $orderDelivery->deliverable;

        $this->assertEquals($address->getKey(), $deliverable->destination_address_id);
    }

    public function typeWithDestinationProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY],
            [OrderDelivery::TYPE_OTHER_DELIVERY],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY],
        ];
    }

    /** @test */
    public function it_changes_the_vehicle_type_of_curri_deliveries()
    {
        $supplier      = Supplier::factory()->createQuietly([
            'address'   => 'supplier address',
            'address_2' => 'supplier address 2',
            'city'      => 'supplier city',
            'state'     => 'supplier state',
            'zip_code'  => 'supplier zip code',
            'country'   => 'supplier country',
        ]);
        $staff         = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->pending()->create(['working_on_it' => 'john doe']);
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        $address       = Address::factory()->create();
        $curriDelivery = CurriDelivery::factory()
            ->usingOrderDelivery($orderDelivery)
            ->car()
            ->usingDestinationAddress($address)
            ->create();

        $data = [
            RequestKeys::TYPE              => OrderDelivery::TYPE_CURRI_DELIVERY,
            RequestKeys::VEHICLE_TYPE      => $vehicleType = CurriDelivery::VEHICLE_TYPE_RACK_TRUCK,
            RequestKeys::USE_STORE_ADDRESS => 1,
        ];

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('getQuote')->withAnyArgs()->once()->andReturn([
            'fee'     => 1200,
            'quoteId' => 'abc-123',
        ]);
        App::bind(Curri::class, fn() => $curri);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->patch(URL::route($this->routeName, $order), $data);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(OrderDeliveryResource::jsonSchema()), $response);
        $this->assertEquals($vehicleType, json_decode($response->getContent())->data->info->vehicle_type);

        $curriDelivery->refresh();
        $this->assertEquals($vehicleType, $curriDelivery->vehicle_type);
    }

    /** @test */
    public function it_updates_fee_to_null_when_order_delivery_is_changed_to_pickup()
    {
        $supplier      = Supplier::factory()->createQuietly([
            'address'   => 'supplier address',
            'address_2' => 'supplier address 2',
            'city'      => 'supplier city',
            'state'     => 'supplier state',
            'zip_code'  => 'supplier zip code',
            'country'   => 'supplier country',
        ]);
        $staff         = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()
            ->usingSupplier($supplier)
            ->pending()
            ->create(['working_on_it' => 'Jon Doe']);
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create(['fee' => 1200]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $data = [
            RequestKeys::TYPE => $type = 'pickup',
        ];

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->patch(URL::route($this->routeName, $order), $data);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(OrderDeliveryResource::jsonSchema()), $response);
        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'id'       => $orderDelivery->getKey(),
            'order_id' => $order->getKey(),
            'type'     => $type,
            'fee'      => null,
        ]);
    }
}
