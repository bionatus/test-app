<?php

namespace Tests\Unit\Models\Pickup;

use App\Models\OrderDelivery;
use App\Models\Pickup;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Pickup $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Pickup::factory()->createQuietly();
    }

    /** @test */
    public function it_is_a_order_delivery()
    {
        $related = $this->instance->orderDelivery()->first();

        $this->assertInstanceOf(OrderDelivery::class, $related);
    }
}
