<?php

namespace Tests\Unit\Models;

use App\Handlers\OrderSubstatus\OrderSubstatusPickupHandler;
use App\Models\IsDeliverable;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use ReflectionClass;

class PickupTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Pickup::tableName(), [
            'id',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_implements_is_deliverable_interface()
    {
        $reflection = new ReflectionClass(Pickup::class);

        $this->assertTrue($reflection->implementsInterface(IsDeliverable::class));
    }

    /** @test */
    public function it_knows_if_uses_a_destination_address()
    {
        $this->assertFalse(Pickup::usesDestinationAddress());
    }

    /** @test */
    public function it_knows_if_uses_an_origin_address()
    {
        $this->assertFalse(Pickup::usesOriginAddress());
    }

    /** @test */
    public function it_knows_if_has_a_destination_address()
    {
        $pickup = Pickup::factory()->createQuietly();

        $this->assertFalse($pickup->hasDestinationAddress());
    }

    /** @test */
    public function it_knows_if_has_an_origin_address()
    {
        $pickup = Pickup::factory()->createQuietly();

        $this->assertFalse($pickup->hasOriginAddress());
    }

    /** @test */
    public function it_creates_an_order_substatus_pickup_handler()
    {
        $order         = Order::factory()->pending()->createQuietly();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        $delivery      = Pickup::factory()->usingOrderDelivery($orderDelivery)->create();

        $handler      = $delivery->createSubstatusHandler($order);
        $currentClass = new ReflectionClass($handler);
        $this->assertSame(OrderSubstatusPickupHandler::class, $currentClass->name);
    }
}
