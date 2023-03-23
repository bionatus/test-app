<?php

namespace Tests\Unit\Models\CurriDelivery;

use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\OrderDelivery;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property CurriDelivery $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = CurriDelivery::factory()->createQuietly();
    }

    /** @test */
    public function it_is_an_order_delivery()
    {
        $related = $this->instance->orderDelivery()->first();

        $this->assertInstanceOf(OrderDelivery::class, $related);
    }

    /** @test */
    public function it_belongs_to_an_origin_address()
    {
        $related = $this->instance->originAddress()->first();

        $this->assertInstanceOf(Address::class, $related);
    }

    /** @test */
    public function it_belongs_to_an_destination_address()
    {
        $related = $this->instance->destinationAddress()->first();

        $this->assertInstanceOf(Address::class, $related);
    }
}
