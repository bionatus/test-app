<?php

namespace Tests\Unit\Models\OrderDelivery;

use App\Models\CurriDelivery;
use App\Models\WarehouseDelivery;
use App\Models\OtherDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use App\Models\ShipmentDelivery;
use Tests\Unit\Models\RelationsTestCase;

class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = OrderDelivery::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_an_order()
    {
        $related = $this->instance->order()->first();

        $this->assertInstanceOf(Order::class, $related);
    }

    /** @test */
    public function it_morph_to_his_type()
    {
        $curriDelivery     = CurriDelivery::factory()->createQuietly();
        $warehouseDelivery = WarehouseDelivery::factory()->createQuietly();
        $otherDelivery     = OtherDelivery::factory()->createQuietly();
        $pickup            = Pickup::factory()->createQuietly();
        $shippingDelivery  = ShipmentDelivery::factory()->createQuietly();

        $this->assertInstanceOf(CurriDelivery::class, $curriDelivery->orderDelivery->deliverable()->first());
        $this->assertInstanceOf(WarehouseDelivery::class, $warehouseDelivery->orderDelivery->deliverable()->first());
        $this->assertInstanceOf(OtherDelivery::class, $otherDelivery->orderDelivery->deliverable()->first());
        $this->assertInstanceOf(Pickup::class, $pickup->orderDelivery->deliverable()->first());
        $this->assertInstanceOf(ShipmentDelivery::class, $shippingDelivery->orderDelivery->deliverable()->first());
    }
}
