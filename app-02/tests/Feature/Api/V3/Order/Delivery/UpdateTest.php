<?php

namespace Tests\Feature\Api\V3\Order\Delivery;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Order\DeliveryController;
use App\Http\Requests\Api\V3\Order\Delivery\UpdateRequest;
use App\Http\Resources\Api\V3\Order\Delivery\BaseResource;
use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\Flag;
use App\Models\ForbiddenZipCode;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OtherDelivery;
use App\Models\Pickup;
use App\Models\ShipmentDelivery;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WarehouseDelivery;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see DeliveryController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ORDER_DELIVERY_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->patch(URL::route($this->routeName,
            [RouteParameters::ORDER => Order::factory()->createQuietly()->getRouteKey()]));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, UpdateRequest::class);
    }

    /** @test */
    public function it_returns_the_correct_base_resource_schema()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create();
        $this->login($user);
        $route    = URL::route($this->routeName, [RouteParameters::ORDER => $order]);
        $response = $this->patch($route, [
            RequestKeys::TYPE                  => 'warehouse_delivery',
            RequestKeys::REQUESTED_START_TIME  => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'Message to the supplier!',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
    }

    /** @test */
    public function it_updates_an_order_delivery()
    {
        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->pickup()->create();
        $startTime     = Carbon::createFromTime(9);
        $endTime       = Carbon::createFromTime(12);

        $this->login($user);
        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order]);
        $this->patch($route, [
            RequestKeys::TYPE                  => $type = 'warehouse_delivery',
            RequestKeys::REQUESTED_START_TIME  => $startTime->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => $endTime->format('H:i'),
            RequestKeys::REQUESTED_DATE        => $date = '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => $address1 = 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => $address2 = 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => $country = 'US',
            RequestKeys::DESTINATION_STATE     => $state = 'New York',
            RequestKeys::DESTINATION_CITY      => $city = 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => $zipCode = '10001',
            RequestKeys::NOTE                  => $note = 'Message to the supplier!',
        ]);

        $timeFormat = 'H:i:s';
        if ('sqlite' === DB::connection()->getName()) {
            $timeFormat = 'H:i';
        }

        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'id'                   => $orderDelivery->getKey(),
            'type'                 => $type,
            'requested_date'       => $date,
            'requested_start_time' => $startTime->format($timeFormat),
            'requested_end_time'   => $endTime->format($timeFormat),
            'date'                 => $date,
            'start_time'           => $startTime->format($timeFormat),
            'end_time'             => $endTime->format($timeFormat),
            'note'                 => $note,
        ]);

        $this->assertDatabaseHas(WarehouseDelivery::tableName(), [
            'id' => $orderDelivery->getKey(),
        ]);

        $this->assertDatabaseCount(Address::tableName(), 1);
        $this->assertDatabaseHas(Address::tableName(), [
            'address_1' => $address1,
            'address_2' => $address2,
            'zip_code'  => $zipCode,
            'city'      => $city,
            'state'     => $state,
            'country'   => $country,
        ]);

        $this->assertDatabaseMissing(Pickup::tableName(), [
            'id' => $orderDelivery->getKey(),
        ]);
    }

    /** @test
     * @dataProvider typeDeliveryProvider
     */
    public function it_updates_a_correct_order_delivery($typeDelivery, $tableName, $oldType, $oldClassName)
    {
        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly(['zip_code' => '123456']);
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create(['type' => $oldType]);
        $oldClassName::factory()->usingOrderDelivery($orderDelivery)->create();
        $date      = '2022-12-20';
        $startTime = Carbon::createFromTime(9);
        $endTime   = Carbon::createFromTime(12);

        $params = [
            RequestKeys::TYPE                 => $typeDelivery,
            RequestKeys::REQUESTED_DATE       => $date,
            RequestKeys::REQUESTED_START_TIME => $startTime->format('H:i'),
            RequestKeys::REQUESTED_END_TIME   => $endTime->format('H:i'),
        ];

        if ($typeDelivery != 'pickup') {
            $params = array_merge($params, [
                RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
                RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
                RequestKeys::DESTINATION_COUNTRY   => 'US',
                RequestKeys::DESTINATION_STATE     => 'New York',
                RequestKeys::DESTINATION_CITY      => 'New York',
                RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            ]);
        }

        $this->login($user);
        $route    = URL::route($this->routeName, [RouteParameters::ORDER => $order]);
        $response = $this->patch($route, $params);

        $response->assertStatus(Response::HTTP_OK);

        $timeFormat = 'H:i:s';
        if ('sqlite' === DB::connection()->getName()) {
            $timeFormat = 'H:i';
        }

        $this->assertDatabaseCount(OrderDelivery::tableName(), 1);
        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'id'                   => $orderDelivery->getKey(),
            'order_id'             => $order->getKey(),
            'type'                 => $typeDelivery,
            'requested_date'       => $date,
            'requested_start_time' => $startTime->format($timeFormat),
            'requested_end_time'   => $endTime->format($timeFormat),
            'date'                 => $date,
            'start_time'           => $startTime->format($timeFormat),
            'end_time'             => $endTime->format($timeFormat),
        ]);

        $this->assertDatabaseHas($tableName, [
            'id' => $orderDelivery->getKey(),
        ]);

        $this->assertDatabaseMissing($oldClassName, [
            'id' => $orderDelivery->getKey(),
        ]);
    }

    public function typeDeliveryProvider(): array
    {
        return [
            //new order delivery type, delivery type table name, old delivery type, old class name delivery type
            [
                OrderDelivery::TYPE_CURRI_DELIVERY,
                CurriDelivery::tableName(),
                OrderDelivery::TYPE_PICKUP,
                Pickup::class,
            ],
            [
                OrderDelivery::TYPE_PICKUP,
                Pickup::tableName(),
                OrderDelivery::TYPE_SHIPMENT_DELIVERY,
                ShipmentDelivery::class,
            ],
            [
                OrderDelivery::TYPE_SHIPMENT_DELIVERY,
                ShipmentDelivery::tableName(),
                OrderDelivery::TYPE_OTHER_DELIVERY,
                OtherDelivery::class,
            ],
            [
                OrderDelivery::TYPE_OTHER_DELIVERY,
                OtherDelivery::tableName(),
                OrderDelivery::TYPE_WAREHOUSE_DELIVERY,
                WarehouseDelivery::class,
            ],
            [
                OrderDelivery::TYPE_WAREHOUSE_DELIVERY,
                WarehouseDelivery::tableName(),
                OrderDelivery::TYPE_CURRI_DELIVERY,
                CurriDelivery::class,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider zipCodesProvider
     */
    public function it_set_the_delivery_type_based_on_supplier_zip_code(
        ?string $zipCode,
        string $deliveryTypeResult
    ) {
        $invalidZipcode = '111111';
        ForbiddenZipCode::factory()->create(['zip_code' => $invalidZipcode]);
        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly(['zip_code' => $zipCode]);
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        $startTime     = Carbon::createFromTime(9);
        $endTime       = Carbon::createFromTime(12);

        $this->login($user);
        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order]);
        $this->patch($route, [
            RequestKeys::TYPE                  => OrderDelivery::TYPE_CURRI_DELIVERY,
            RequestKeys::REQUESTED_START_TIME  => $startTime->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => $endTime->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '456132',
        ]);

        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'id'       => $orderDelivery->getKey(),
            'order_id' => $order->getKey(),
            'type'     => $deliveryTypeResult,
        ]);
    }

    public function zipCodesProvider(): array
    {
        return [
            ['111111', OrderDelivery::TYPE_WAREHOUSE_DELIVERY],
            ['123456', OrderDelivery::TYPE_CURRI_DELIVERY],
            [null, OrderDelivery::TYPE_WAREHOUSE_DELIVERY],
        ];
    }

    /**
     * @test
     * @dataProvider flagProvider
     */
    public function it_set_the_delivery_type_based_on_supplier_forbidden_curri_flag(
        bool $flag,
        string $deliveryTypeResult
    ) {

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly(['zip_code' => '1111']);
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        $startTime     = Carbon::createFromTime(9);
        $endTime       = Carbon::createFromTime(12);

        if ($flag) {
            Flag::factory()->usingModel($supplier)->create(['name' => Flag::FORBIDDEN_CURRI]);
        }

        $this->login($user);
        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order]);
        $this->patch($route, [
            RequestKeys::TYPE                  => OrderDelivery::TYPE_CURRI_DELIVERY,
            RequestKeys::REQUESTED_START_TIME  => $startTime->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => $endTime->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '456132',
        ]);

        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'id'       => $orderDelivery->getKey(),
            'order_id' => $order->getKey(),
            'type'     => $deliveryTypeResult,
        ]);
    }

    public function flagProvider(): array
    {
        return [
            [true, OrderDelivery::TYPE_WAREHOUSE_DELIVERY],
            [false, OrderDelivery::TYPE_CURRI_DELIVERY],
        ];
    }
}
