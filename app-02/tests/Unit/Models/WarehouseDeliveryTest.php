<?php

namespace Tests\Unit\Models;

use App\Handlers\OrderSubstatus\OrderSubstatusShipmentHandler;
use App\Models\IsDeliverable;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\WarehouseDelivery;
use ReflectionClass;

class WarehouseDeliveryTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(WarehouseDelivery::tableName(), [
            'id',
            'destination_address_id',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_implements_is_deliverable_interface()
    {
        $reflection = new ReflectionClass(WarehouseDelivery::class);

        $this->assertTrue($reflection->implementsInterface(IsDeliverable::class));
    }

    /** @test */
    public function it_knows_if_uses_a_destination_address()
    {
        $this->assertTrue(WarehouseDelivery::usesDestinationAddress());
    }

    /** @test */
    public function it_knows_if_uses_an_origin_address()
    {
        $this->assertFalse(WarehouseDelivery::usesOriginAddress());
    }

    /**
     * @test
     * @dataProvider addressProvider
     */
    public function it_knows_if_has_a_destination_address($hasAddress)
    {
        $extra = [];
        if (!$hasAddress) {
            $extra = ['destination_address_id' => null];
        }
        $warehouseDelivery = WarehouseDelivery::factory()->createQuietly($extra);

        $this->assertSame($hasAddress, $warehouseDelivery->hasDestinationAddress());
    }

    public function addressProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /** @test */
    public function it_knows_if_has_an_origin_address()
    {
        $warehouseDelivery = WarehouseDelivery::factory()->createQuietly();

        $this->assertFalse($warehouseDelivery->hasOriginAddress());
    }

    /** @test */
    public function it_creates_an_order_substatus_shipment_handler()
    {
        //@NOTE WarehouseDelivery doesn't have a SubstatusHandler.
        $order         = Order::factory()->pending()->createQuietly();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->warehouseDelivery()->create();
        $delivery      = WarehouseDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $handler      = $delivery->createSubstatusHandler($order);
        $currentClass = new ReflectionClass($handler);
        $this->assertSame(OrderSubstatusShipmentHandler::class, $currentClass->name);
    }
}
