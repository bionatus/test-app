<?php

namespace Tests\Unit\Models\Item;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartOrder;
use App\Models\CartOrderItem;
use App\Models\CustomItem;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\ItemOrderSnap;
use App\Models\ItemWishlist;
use App\Models\Order;
use App\Models\OrderSnap;
use App\Models\Part;
use App\Models\Supply;
use App\Models\Wishlist;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Item $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Item::factory()->part()->create();
    }

    /** @test */
    public function it_is_a_part()
    {
        Part::factory()->create(['id' => $this->instance->id]);
        $related = $this->instance->part()->first();

        $this->assertInstanceOf(Part::class, $related);
    }

    /** @test */
    public function it_is_a_custom_item()
    {
        CustomItem::factory()->create(['id' => $this->instance->id]);
        $related = $this->instance->customItem()->first();

        $this->assertInstanceOf(CustomItem::class, $related);
    }

    /** @test */
    public function it_has_orders()
    {
        ItemOrder::factory()->usingItem($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->orders()->get();

        $this->assertCorrectRelation($related, Order::class);
    }

    /** @test */
    public function it_has_item_orders()
    {
        ItemOrder::factory()->usingItem($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->itemOrders()->get();

        $this->assertCorrectRelation($related, ItemOrder::class);
    }

    /** @test */
    public function it_morph_to_his_type()
    {
        $part   = Part::factory()->create();
        $supply = Supply::factory()->create();
        $customItem = CustomItem::factory()->create();

        $this->assertInstanceOf(Part::class, $part->item->orderable()->first());
        $this->assertInstanceOf(Supply::class, $supply->item->orderable()->first());
        $this->assertInstanceOf(CustomItem::class, $customItem->item->orderable()->first());
    }

    /** @test */
    public function it_has_carts()
    {
        CartItem::factory()->usingItem($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->carts()->get();

        $this->assertCorrectRelation($related, Cart::class);
    }

    /** @test */
    public function it_has_cart_items()
    {
        CartItem::factory()->usingItem($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->cartItems()->get();

        $this->assertCorrectRelation($related, CartItem::class);
    }

    /** @test */
    public function it_has_cart_orders()
    {
        CartOrderItem::factory()->usingItem($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->cartOrders()->get();

        $this->assertCorrectRelation($related, CartOrder::class);
    }

    /** @test */
    public function it_has_cart_order_items()
    {
        CartOrderItem::factory()->usingItem($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->cartOrderItems()->get();

        $this->assertCorrectRelation($related, CartOrderItem::class);
    }

    /** @test */
    public function it_has_wishlists()
    {
        ItemWishlist::factory()->usingItem($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->wishlists()->get();

        $this->assertCorrectRelation($related, Wishlist::class);
    }

    /** @test */
    public function it_has_item_wishlists()
    {
        ItemWishlist::factory()->usingItem($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->itemWishlists()->get();

        $this->assertCorrectRelation($related, ItemWishlist::class);
    }

    public function it_has_order_snaps()
    {
        ItemOrderSnap::factory()->usingItem($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->orderSnaps()->get();

        $this->assertCorrectRelation($related, OrderSnap::class);
    }

    /** @test */
    public function it_has_item_order_snaps()
    {
        ItemOrderSnap::factory()->usingItem($this->instance)->count(self::COUNT)->createQuietly();

        $related = $this->instance->itemOrderSnaps()->get();

        $this->assertCorrectRelation($related, ItemOrderSnap::class);
    }
}
