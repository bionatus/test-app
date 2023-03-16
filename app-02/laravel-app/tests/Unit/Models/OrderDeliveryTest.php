<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use Carbon\Carbon;

class OrderDeliveryTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(OrderDelivery::tableName(), [
            'id',
            'order_id',
            'type',
            'requested_date',
            'requested_start_time',
            'requested_end_time',
            'date',
            'start_time',
            'end_time',
            'note',
            'fee',
            'is_needed_now',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_saves_fee_as_cents()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create(['fee' => $fee = 100]);

        $this->assertDatabaseHas(OrderDelivery::tableName(), ['fee' => $fee * 100]);

        $this->assertSame($fee, $orderDelivery->fee);
    }

    /** @test */
    public function it_knows_if_is_a_pickup()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->pickup()->create();

        $this->assertTrue($orderDelivery->isPickup());
        $this->assertFalse($orderDelivery->isCurriDelivery());
        $this->assertFalse($orderDelivery->isWarehouseDelivery());
        $this->assertFalse($orderDelivery->isOtherDelivery());
        $this->assertFalse($orderDelivery->isShipmentDelivery());
    }

    /** @test */
    public function it_knows_if_is_a_curri_delivery()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();

        $this->assertTrue($orderDelivery->isCurriDelivery());
        $this->assertFalse($orderDelivery->isPickup());
        $this->assertFalse($orderDelivery->isWarehouseDelivery());
        $this->assertFalse($orderDelivery->isOtherDelivery());
        $this->assertFalse($orderDelivery->isShipmentDelivery());
    }

    /** @test */
    public function it_knows_if_is_a_warehouse_delivery()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->warehouseDelivery()->create();

        $this->assertTrue($orderDelivery->isWarehouseDelivery());
        $this->assertFalse($orderDelivery->isOtherDelivery());
        $this->assertFalse($orderDelivery->isPickup());
        $this->assertFalse($orderDelivery->isCurriDelivery());
        $this->assertFalse($orderDelivery->isShipmentDelivery());
    }

    /** @test */
    public function it_knows_if_is_a_other_delivery()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->otherDelivery()->create();

        $this->assertTrue($orderDelivery->isOtherDelivery());
        $this->assertFalse($orderDelivery->isWarehouseDelivery());
        $this->assertFalse($orderDelivery->isPickup());
        $this->assertFalse($orderDelivery->isCurriDelivery());
        $this->assertFalse($orderDelivery->isShipmentDelivery());
    }

    /** @test */
    public function it_knows_if_is_a_shipment_delivery()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->shipmentDelivery()->create();

        $this->assertTrue($orderDelivery->isShipmentDelivery());
        $this->assertFalse($orderDelivery->isPickup());
        $this->assertFalse($orderDelivery->isCurriDelivery());
        $this->assertFalse($orderDelivery->isWarehouseDelivery());
        $this->assertFalse($orderDelivery->isOtherDelivery());
    }

    /** @test
     * @dataProvider deliveryTypeProvider
     */
    public function it_knows_if_is_a_delivery(string $deliveryType, bool $expected)
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create(['type' => $deliveryType]);

        $this->assertSame($expected, $orderDelivery->isDelivery());
    }

    public function deliveryTypeProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, true],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, true],
            [OrderDelivery::TYPE_OTHER_DELIVERY, true],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, true],
            [OrderDelivery::TYPE_PICKUP, false],
        ];
    }

    /** @test
     * @dataProvider supplierDeliveryDates
     */
    public function it_knows_if_date_time_is_valid_for_supplier(string $now, bool $expected)
    {
        Carbon::setTestNow($now);

        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => '2022-11-08',
            'start_time' => Carbon::createFromTime(9)->format('H:i:s'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i:s'),
        ]);

        $this->assertSame($expected, $orderDelivery->isAValidDateTimeForSupplier());
    }

    public function supplierDeliveryDates(): array
    {
        return [
            // $now, $expected
            ['2022-11-09 00:00:00', false],
            ['2022-11-08 12:00:00', false],
            ['2022-11-08 11:59:00', true],
            ['2022-11-08 10:00:00', true],
        ];
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_knows_if_it_is_needed_now(bool $isNeededNow)
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create([
            'is_needed_now' => $isNeededNow,
        ]);

        $this->assertSame($isNeededNow, $orderDelivery->isNeededNow());
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_knows_if_it_is_needed_later(bool $isNeededNow)
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create([
            'is_needed_now' => $isNeededNow,
        ]);

        $this->assertSame(!$isNeededNow, $orderDelivery->isNeededLater());
    }

    public function dataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /** @test */
    public function it_has_a_readable_type_attribute()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create([
            'type' => 'readable_type_test',
        ]);

        $this->assertSame('Readable Type Test', $orderDelivery->readableType);
    }

    /** @test */
    public function it_has_a_time_range_attribute()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create([
            'start_time' => Carbon::createFromTime(9)->format('H:i:s'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i:s'),
        ]);

        $this->assertSame('9AM - 12PM', $orderDelivery->time_range);
    }

    /** @test */
    public function it_will_not_log_activity_on_create()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create();

        $this->assertEquals(0, $orderDelivery->activities->count());
    }

    /** @test */
    public function it_will_log_activity_on_update()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create(['note' => $oldNote = 'old note']);
        $orderDelivery->update(['note' => $newNote = 'new note']);

        $this->assertEquals(1, $orderDelivery->activities->count());
        $this->assertDatabaseHas('activity_log', [
            'log_name'                     => 'order_log',
            'description'                  => 'order_delivery.updated',
            'subject_type'                 => 'order_delivery',
            'subject_id'                   => $orderDelivery->getKey(),
            'resource'                     => 'order_delivery',
            'event'                        => 'updated',
            'properties->old->note'        => $oldNote,
            'properties->attributes->note' => $newNote,
        ]);
    }

    /** @test */
    public function it_returns_formatted_date_and_start_time()
    {
        $now = Carbon::now();

        $expected      = $now->format('Y-m-d H:i');
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => $now->format('Y-m-d'),
            'start_time' => $now->format('H:i'),
        ]);

        $this->assertSame($expected, $orderDelivery->startTime()->format('Y-m-d H:i'));
    }
}
