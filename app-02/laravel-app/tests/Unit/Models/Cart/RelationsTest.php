<?php

namespace Tests\Unit\Models\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Cart $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Cart::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_has_items()
    {
        CartItem::factory()->usingCart($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->items()->get();

        $this->assertCorrectRelation($related, Item::class);
    }

    /** @test */
    public function it_has_cart_items()
    {
        CartItem::factory()->usingCart($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->cartItems()->get();

        $this->assertCorrectRelation($related, CartItem::class);
    }

    /** @test */
    public function it_belongs_to_a_supplier()
    {
        $supplier = Supplier::factory()->createQuietly();
        $cart     = Cart::factory()->usingSupplier($supplier)->create();
        $related  = $cart->supplier()->first();

        $this->assertInstanceOf(Supplier::class, $related);
    }
}
