<?php

namespace Tests\Unit\Handler;

use App\Handlers\OrderDeliveryHandler;
use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\ShipmentDelivery;
use App\Models\ShipmentDeliveryPreference;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WarehouseDelivery;
use App\Types\Money;
use DB;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderDeliveryHandlerTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @dataProvider deliveryTypeProvider
     */
    public function it_creates_an_order_delivery($deliveryType)
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();

        $this->assertDatabaseMissing(OrderDelivery::tableName(), [
            'order_id' => $order->getKey(),
        ]);

        $startTime = Carbon::createFromTime('9');
        $endTime   = Carbon::createFromTime('17');

        $data = [
            'type'                 => $deliveryType,
            'requested_date'       => $date = '2022-11-14',
            'requested_start_time' => $startTime->format('H:i'),
            'requested_end_time'   => $endTime->format('H:i'),
            'note'                 => $note = 'long text.......',
            'fee'                  => $fee = 50,
        ];

        $handler = new OrderDeliveryHandler($order);
        $handler->createOrUpdateDelivery($data);

        $timeFormat = 'H:i:s';
        if ('sqlite' === DB::connection()->getName()) {
            $timeFormat = 'H:i';
        }

        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'order_id'             => $order->getKey(),
            'type'                 => $deliveryType,
            'requested_date'       => $date,
            'requested_start_time' => $startTime->format($timeFormat),
            'requested_end_time'   => $endTime->format($timeFormat),
            'note'                 => $note,
            'fee'                  => Money::toCents($fee),
        ]);
    }

    /** @test
     * @dataProvider deliveryTypeProvider
     */
    public function it_updates_an_order_delivery($deliveryType)
    {
        $user            = User::factory()->create();
        $supplier        = Supplier::factory()->createQuietly();
        $order           = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $oldDeliveryType = 'pickup';
        if ($deliveryType == 'pickup') {
            $oldDeliveryType = 'warehouse_delivery';
        }
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create(['type' => $oldDeliveryType]);

        $startTime = Carbon::createFromTime('9');
        $endTime   = Carbon::createFromTime('17');

        $data = [
            'type'                 => $deliveryType,
            'requested_date'       => $date = '2022-11-14',
            'requested_start_time' => $startTime->format('H:i'),
            'requested_end_time'   => $endTime->format('H:i'),
            'note'                 => $note = 'long text.......',
            'fee'                  => $fee = 50,
        ];

        $handler = new OrderDeliveryHandler($order);
        $handler->createOrUpdateDelivery($data);

        $timeFormat = 'H:i:s';
        if ('sqlite' === DB::connection()->getName()) {
            $timeFormat = 'H:i';
        }

        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'id'                   => $orderDelivery->getKey(),
            'type'                 => $deliveryType,
            'requested_date'       => $date,
            'requested_start_time' => $startTime->format($timeFormat),
            'requested_end_time'   => $endTime->format($timeFormat),
            'note'                 => $note,
            'fee'                  => Money::toCents($fee),
        ]);
    }

    /** @test */
    public function it_creates_an_origin_address_without_old_address()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $data     = [
            'address_1' => $address = 'origin address 123',
            'address_2' => null,
            'country'   => $country = 'US',
            'state'     => $state = 'US-NY',
            'city'      => $city = 'New York',
            'zip_code'  => $zipCode = '10001',
        ];

        $handler       = new OrderDeliveryHandler($order);
        $originAddress = $handler->createOrUpdateOriginAddress($data);

        $this->assertDatabaseHas(Address::tableName(), [
            'id'        => $originAddress->getKey(),
            'address_1' => $address,
            'country'   => $country,
            'state'     => $state,
            'city'      => $city,
            'zip_code'  => $zipCode,
        ]);
    }

    /** @test */
    public function it_updates_an_origin_address_with_old_address()
    {
        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        $originAddress = Address::factory()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->usingOriginAddress($originAddress)->create();

        $data = [
            'address_1' => $address = 'origin address 123',
            'address_2' => null,
            'country'   => $country = 'US',
            'state'     => $state = 'US-NY',
            'city'      => $city = 'New York',
            'zip_code'  => $zipCode = '10001',
        ];

        $handler = new OrderDeliveryHandler($order);
        $handler->createOrUpdateOriginAddress($data);

        $this->assertDatabaseHas(Address::tableName(), [
            'id'        => $originAddress->getKey(),
            'address_1' => $address,
            'country'   => $country,
            'state'     => $state,
            'city'      => $city,
            'zip_code'  => $zipCode,
        ]);
    }

    /** @test */
    public function it_creates_an_destination_address_without_old_address()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $data     = [
            'address_1' => $address = 'destination address 2020',
            'address_2' => null,
            'country'   => $country = 'US',
            'state'     => $state = 'US-NY',
            'city'      => $city = 'New York',
            'zip_code'  => $zipCode = '10001',
        ];

        $handler            = new OrderDeliveryHandler($order);
        $destinationAddress = $handler->createOrUpdateDestinationAddress($data);

        $this->assertDatabaseHas(Address::tableName(), [
            'id'        => $destinationAddress->getKey(),
            'address_1' => $address,
            'country'   => $country,
            'state'     => $state,
            'city'      => $city,
            'zip_code'  => $zipCode,
        ]);
    }

    /** @test */
    public function it_updates_an_destination_address_with_old_address()
    {
        $user               = User::factory()->create();
        $supplier           = Supplier::factory()->createQuietly();
        $order              = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $orderDelivery      = OrderDelivery::factory()->usingOrder($order)->warehouseDelivery()->create();
        $destinationAddress = Address::factory()->create();
        WarehouseDelivery::factory()
            ->usingOrderDelivery($orderDelivery)
            ->usingDestinationAddress($destinationAddress)
            ->create();

        $data = [
            'address_1' => $address = 'destination address 2056',
            'address_2' => null,
            'country'   => $country = 'US',
            'state'     => $state = 'US-NY',
            'city'      => $city = 'New York',
            'zip_code'  => $zipCode = '10001',
        ];

        $handler = new OrderDeliveryHandler($order);
        $handler->createOrUpdateDestinationAddress($data);

        $this->assertDatabaseHas(Address::tableName(), [
            'id'        => $destinationAddress->getKey(),
            'address_1' => $address,
            'country'   => $country,
            'state'     => $state,
            'city'      => $city,
            'zip_code'  => $zipCode,
        ]);
    }

    /** @test
     * @dataProvider deliveryTypeProvider
     */
    public function it_creates_a_new_delivery_type($deliveryType)
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $data     = [
            'type'                 => $deliveryType,
            'requested_date'       => '2022-11-14',
            'requested_start_time' => '09:00',
            'requested_end_time'   => '12:00',
            'note'                 => 'long text.......',
            'fee'                  => 50,
        ];

        $destinationAddress           = Address::factory()->create();
        $shipmentDeliveryPreferenceId = 1;
        ShipmentDeliveryPreference::factory()->create([
            'id' => $shipmentDeliveryPreferenceId,
        ]);

        $handler       = new OrderDeliveryHandler($order);
        $orderDelivery = $handler->createOrUpdateDelivery($data);

        $dataTypeDelivery = ['shipment_delivery_preference_id' => $shipmentDeliveryPreferenceId];

        $deliverable          = $handler->createOrUpdateDeliveryType($destinationAddress, null, $dataTypeDelivery);
        $newDeliveryTypeClass = Relation::morphMap()[$deliveryType];
        if ($orderDelivery->isShipmentDelivery()) {
            $this->assertDatabaseHas(ShipmentDelivery::tableName(), [
                'shipment_delivery_preference_id' => $shipmentDeliveryPreferenceId,
            ]);
        }

        $databaseHas = ['id' => $deliverable->getKey()];
        if ($deliverable->usesDestinationAddress()) {
            $databaseHas['destination_address_id'] = $destinationAddress->getKey();
        }

        $this->assertDatabaseHas($newDeliveryTypeClass, $databaseHas);
    }

    /** @test
     * @dataProvider changeDeliveryTypeProvider
     */
    public function it_updates_a_delivery_type(
        string $oldDeliveryType,
        string $newDeliveryType
    ) {
        $user               = User::factory()->create();
        $supplier           = Supplier::factory()->createQuietly();
        $order              = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $orderDelivery      = OrderDelivery::factory()->usingOrder($order)->create(['type' => $oldDeliveryType]);
        $originAddress      = Address::factory()->create();
        $destinationAddress = Address::factory()->create();
        $oldClassName       = Relation::morphMap()[$oldDeliveryType];
        $dataType           = ['id' => $orderDelivery->getKey()];

        $newDataOrderDelivery = [
            'type'                 => $newDeliveryType,
            'requested_date'       => '2022-11-14',
            'requested_start_time' => '09:00',
            'requested_end_time'   => '12:00',
            'note'                 => 'long text.......',
            'fee'                  => 50,
        ];

        if ($orderDelivery->isDelivery()) {
            $dataType['destination_address_id'] = $destinationAddress->getKey();
            if ($oldDeliveryType == OrderDelivery::TYPE_CURRI_DELIVERY) {
                $dataType['origin_address_id'] = $originAddress->getKey();
            }
        }
        $orderDelivery->deliverable()->create($dataType);

        $handler = new OrderDeliveryHandler($order);
        $handler->createOrUpdateDelivery($newDataOrderDelivery);
        $deliverable = $handler->createOrUpdateDeliveryType($destinationAddress, $originAddress);

        $newDeliveryTypeClass = Relation::morphMap()[$newDeliveryType];
        $databaseHas          = ['id' => $orderDelivery->getKey()];
        if ($deliverable->usesDestinationAddress()) {
            $databaseHas['destination_address_id'] = $destinationAddress->getKey();
        }
        $this->assertDatabaseHas($newDeliveryTypeClass, $databaseHas);

        if ($oldDeliveryType !== $newDeliveryType) {
            $this->assertDatabaseMissing($oldClassName, [
                'id' => $orderDelivery->getKey(),
            ]);
        }

        if (!$deliverable->usesDestinationAddress() && $oldClassName::usesDestinationAddress()) {
            $this->assertDatabaseMissing(Address::tableName(), [
                'id' => $destinationAddress->getKey(),
            ]);
        }

        if (!$deliverable->usesOriginAddress() && $oldClassName::usesOriginAddress()) {
            $this->assertDatabaseMissing(Address::tableName(), [
                'id' => $originAddress->getKey(),
            ]);
        }
    }

    /** @test
     * @dataProvider changeDeliveryTypeProvider
     */
    public function it_returns_the_old_delivery_type(
        string $oldDeliveryType,
        string $newDeliveryType
    ) {
        $user               = User::factory()->create();
        $supplier           = Supplier::factory()->createQuietly();
        $order              = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $orderDelivery      = OrderDelivery::factory()->usingOrder($order)->create(['type' => $oldDeliveryType]);
        $originAddress      = Address::factory()->create();
        $destinationAddress = Address::factory()->create();
        $dataOrderDelivery  = [
            'type'                 => $oldDeliveryType,
            'requested_date'       => '2022-11-14',
            'requested_start_time' => '09:00',
            'requested_end_time'   => '12:00',
            'note'                 => 'long text.......',
            'fee'                  => 50,
        ];
        $dataType           = ['id' => $orderDelivery->getKey()];

        if ($orderDelivery->isDelivery()) {
            $dataType['destination_address_id'] = $destinationAddress->getKey();
            if ($oldDeliveryType == OrderDelivery::TYPE_CURRI_DELIVERY) {
                $dataType['origin_address_id'] = $originAddress->getKey();
            }
        }
        $orderDelivery->deliverable()->create($dataType);

        $handler = new OrderDeliveryHandler($order);
        $handler->createOrUpdateDelivery($dataOrderDelivery);
        $expectedDeliveryType = $handler->createOrUpdateDeliveryType($destinationAddress, $originAddress);

        $dataOrderDelivery['type'] = $newDeliveryType;
        $handler->createOrUpdateDelivery($dataOrderDelivery);
        $handler->createOrUpdateDeliveryType($destinationAddress, $originAddress);

        $oldType = $handler->getOldDeliveryType();
        $this->assertSame($expectedDeliveryType, $oldType);
    }

    /** @test */
    public function it_returns_the_delivery_type()
    {
        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create(['type' => OrderDelivery::TYPE_PICKUP]);
        $orderDelivery->deliverable()->create(['id' => $orderDelivery->getKey()]);

        $destinationAddress = Address::factory()->create();
        $dataOrderDelivery  = [
            'type'                 => OrderDelivery::TYPE_WAREHOUSE_DELIVERY,
            'requested_date'       => '2022-11-14',
            'requested_start_time' => '09:00',
            'requested_end_time'   => '12:00',
            'note'                 => 'long text.......',
            'fee'                  => 50,
        ];

        $handler = new OrderDeliveryHandler($order);

        $this->assertSame($handler->getType(), $orderDelivery->type);

        $newOrderDelivery = $handler->createOrUpdateDelivery($dataOrderDelivery);
        $handler->createOrUpdateDeliveryType($destinationAddress);

        $this->assertSame($handler->getType(), $newOrderDelivery->type);
    }

    public function changeDeliveryTypeProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, OrderDelivery::TYPE_CURRI_DELIVERY],
            [OrderDelivery::TYPE_CURRI_DELIVERY, OrderDelivery::TYPE_OTHER_DELIVERY],
            [OrderDelivery::TYPE_CURRI_DELIVERY, OrderDelivery::TYPE_PICKUP],
            [OrderDelivery::TYPE_CURRI_DELIVERY, OrderDelivery::TYPE_SHIPMENT_DELIVERY],
            [OrderDelivery::TYPE_CURRI_DELIVERY, OrderDelivery::TYPE_WAREHOUSE_DELIVERY],
            [OrderDelivery::TYPE_OTHER_DELIVERY, OrderDelivery::TYPE_CURRI_DELIVERY],
            [OrderDelivery::TYPE_OTHER_DELIVERY, OrderDelivery::TYPE_OTHER_DELIVERY],
            [OrderDelivery::TYPE_OTHER_DELIVERY, OrderDelivery::TYPE_PICKUP],
            [OrderDelivery::TYPE_OTHER_DELIVERY, OrderDelivery::TYPE_SHIPMENT_DELIVERY],
            [OrderDelivery::TYPE_OTHER_DELIVERY, OrderDelivery::TYPE_WAREHOUSE_DELIVERY],
            [OrderDelivery::TYPE_PICKUP, OrderDelivery::TYPE_CURRI_DELIVERY],
            [OrderDelivery::TYPE_PICKUP, OrderDelivery::TYPE_OTHER_DELIVERY],
            [OrderDelivery::TYPE_PICKUP, OrderDelivery::TYPE_PICKUP],
            [OrderDelivery::TYPE_PICKUP, OrderDelivery::TYPE_SHIPMENT_DELIVERY],
            [OrderDelivery::TYPE_PICKUP, OrderDelivery::TYPE_WAREHOUSE_DELIVERY],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, OrderDelivery::TYPE_PICKUP],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, OrderDelivery::TYPE_CURRI_DELIVERY],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, OrderDelivery::TYPE_OTHER_DELIVERY],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, OrderDelivery::TYPE_SHIPMENT_DELIVERY],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, OrderDelivery::TYPE_WAREHOUSE_DELIVERY],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, OrderDelivery::TYPE_PICKUP],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, OrderDelivery::TYPE_CURRI_DELIVERY],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, OrderDelivery::TYPE_OTHER_DELIVERY],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, OrderDelivery::TYPE_SHIPMENT_DELIVERY],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, OrderDelivery::TYPE_WAREHOUSE_DELIVERY],
        ];
    }

    public function deliveryTypeProvider(): array
    {
        return [
            [OrderDelivery::TYPE_PICKUP],
            [OrderDelivery::TYPE_CURRI_DELIVERY],
            [OrderDelivery::TYPE_OTHER_DELIVERY],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY],
        ];
    }
}
