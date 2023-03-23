<?php

namespace Tests\Unit\Models\CartItem;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property CartItem $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = CartItem::factory()->create();
    }

    /** @test */
    public function it_belongs_to_an_item()
    {
        $related = $this->instance->item()->first();

        $this->assertInstanceOf(Item::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_cart()
    {
        $related = $this->instance->cart()->first();

        $this->assertInstanceOf(Cart::class, $related);
    }
}
