<?php

namespace Tests\Unit\Models\CartOrder;

use App\Models\Item;
use App\Models\Order;
use App\Models\CartOrder;
use App\Models\CartOrderItem;

use Tests\Unit\Models\RelationsTestCase;

/**
 * @property CartOrder $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = CartOrder::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_a_order()
    {
        $related = $this->instance->order()->first();

        $this->assertInstanceOf(Order::class, $related);
    }

    /** @test */
    public function it_has_items()
    {
        CartOrderItem::factory()->usingCartOrder($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->items()->get();

        $this->assertCorrectRelation($related, Item::class);
    }

    /** @test */
    public function it_has_cart_order_items()
    {
        CartOrderItem::factory()->usingCartOrder($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->cartOrderItems()->get();

        $this->assertCorrectRelation($related, CartOrderItem::class);
    }
}
