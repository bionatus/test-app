<?php

namespace Tests\Unit\Models\Wishlist;

use App\Models\Item;
use App\Models\ItemWishlist;
use App\Models\User;
use App\Models\Wishlist;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Wishlist $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Wishlist::factory()->create();
    }

    /** @test */
    public function it_has_items()
    {
        ItemWishlist::factory()->usingWishlist($this->instance)->count(10)->create();

        $related = $this->instance->items()->get();

        $this->assertCorrectRelation($related, Item::class);
    }

    /** @test */
    public function it_has_item_wishlist()
    {
        ItemWishlist::factory()->usingWishlist($this->instance)->count(10)->create();

        $related = $this->instance->itemWishlists()->get();

        $this->assertCorrectRelation($related, ItemWishlist::class);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }
}
