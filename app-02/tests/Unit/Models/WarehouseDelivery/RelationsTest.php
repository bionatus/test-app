<?php

namespace Tests\Unit\Models\WarehouseDelivery;

use App\Models\Address;
use App\Models\WarehouseDelivery;
use App\Models\OrderDelivery;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property WarehouseDelivery $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = WarehouseDelivery::factory()->createQuietly();
    }

    /** @test */
    public function it_is_an_order_delivery()
    {
        $related = $this->instance->orderDelivery()->first();

        $this->assertInstanceOf(OrderDelivery::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_destination_address()
    {
        $related = $this->instance->destinationAddress()->first();

        $this->assertInstanceOf(Address::class, $related);
    }
}
