<?php

namespace Tests\Unit\Models\ItemWishlist;

use App\Models\Item;
use App\Models\ItemWishlist;
use App\Models\Wishlist;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property ItemWishlist $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = ItemWishlist::factory()->create();
    }

    /** @test */
    public function it_belongs_to_an_item()
    {
        $related = $this->instance->item()->first();

        $this->assertInstanceOf(Item::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_wishlist()
    {
        $related = $this->instance->wishlist()->first();

        $this->assertInstanceOf(Wishlist::class, $related);
    }
}
