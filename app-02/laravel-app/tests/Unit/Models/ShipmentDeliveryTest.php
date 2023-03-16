<?php

namespace Tests\Unit\Models;

use App\Handlers\OrderSubstatus\OrderSubstatusShipmentHandler;
use App\Models\IsDeliverable;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\ShipmentDelivery;
use ReflectionClass;

class ShipmentDeliveryTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(ShipmentDelivery::tableName(), [
            'id',
            'destination_address_id',
            'shipment_delivery_preference_id',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_implements_is_deliverable_interface()
    {
        $reflection = new ReflectionClass(ShipmentDelivery::class);

        $this->assertTrue($reflection->implementsInterface(IsDeliverable::class));
    }

    /** @test */
    public function it_knows_if_uses_a_destination_address()
    {
        $this->assertTrue(ShipmentDelivery::usesDestinationAddress());
    }

    /** @test */
    public function it_knows_if_uses_an_origin_address()
    {
        $this->assertFalse(ShipmentDelivery::usesOriginAddress());
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
        $shipmentDelivery = ShipmentDelivery::factory()->createQuietly($extra);

        $this->assertSame($hasAddress, $shipmentDelivery->hasDestinationAddress());
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
        $shipmentDelivery = ShipmentDelivery::factory()->createQuietly();

        $this->assertFalse($shipmentDelivery->hasOriginAddress());
    }

    /** @test */
    public function it_creates_an_order_substatus_shipment_handler()
    {
        $order         = Order::factory()->pending()->createQuietly();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->shipmentDelivery()->create();
        $delivery      = ShipmentDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $handler      = $delivery->createSubstatusHandler($order);
        $currentClass = new ReflectionClass($handler);
        $this->assertSame(OrderSubstatusShipmentHandler::class, $currentClass->name);
    }
}
