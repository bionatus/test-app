<?php

namespace Tests\Unit\Models\Address;

use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\WarehouseDelivery;
use App\Models\OtherDelivery;
use App\Models\ShipmentDelivery;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Address $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Address::factory()->create();
    }

    /** @test */
    public function it_has_warehouse_delivery()
    {
        WarehouseDelivery::factory()->usingDestinationAddress($this->instance)->createQuietly();
        $related = $this->instance->warehouseDelivery;

        $this->assertInstanceOf(WarehouseDelivery::class, $related);
    }

    /** @test */
    public function it_has_other_delivery()
    {
        OtherDelivery::factory()->usingDestinationAddress($this->instance)->createQuietly();
        $related = $this->instance->otherDelivery;

        $this->assertInstanceOf(OtherDelivery::class, $related);
    }

    /** @test */
    public function it_has_shippment_delivery()
    {
        ShipmentDelivery::factory()->usingDestinationAddress($this->instance)->createQuietly();
        $related = $this->instance->shipmentDelivery;

        $this->assertInstanceOf(ShipmentDelivery::class, $related);
    }

    /** @test */
    public function it_has_curri_origin_delivery()
    {
        CurriDelivery::factory()->usingOriginAddress($this->instance)->createQuietly();
        $related = $this->instance->originCurriDelivery;

        $this->assertInstanceOf(CurriDelivery::class, $related);
    }

    /** @test */
    public function it_has_curri_destination_delivery()
    {
        CurriDelivery::factory()->usingDestinationAddress($this->instance)->createQuietly();
        $related = $this->instance->destinationCurriDelivery;

        $this->assertInstanceOf(CurriDelivery::class, $related);
    }
}
