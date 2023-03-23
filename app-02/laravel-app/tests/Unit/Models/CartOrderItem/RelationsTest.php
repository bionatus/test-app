<?php

namespace Tests\Unit\Models\CartOrderItem;

use App\Models\CartOrder;
use App\Models\CartOrderItem;
use App\Models\Item;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property CartOrderItem $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = CartOrderItem::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_an_item()
    {
        $related = $this->instance->item()->first();

        $this->assertInstanceOf(Item::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_cart_order()
    {
        $related = $this->instance->cartOrder()->first();

        $this->assertInstanceOf(CartOrder::class, $related);
    }
}
