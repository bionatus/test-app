<?php

namespace Tests\Unit\Models\ItemWishlist\Scopes;

use App\Models\ItemWishlist;
use App\Models\ItemWishlist\Scopes\ByWishlist;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByWishlistTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_wishlist()
    {
        $wishlist     = Wishlist::factory()->create();
        $itemWishlist = ItemWishlist::factory()->usingWishlist($wishlist)->create();
        ItemWishlist::factory()->create();

        $filtered = ItemWishlist::scoped(new ByWishlist($wishlist))->first();

        $this->assertInstanceOf(ItemWishlist::class, $filtered);
        $this->assertSame($itemWishlist->getKey(), $filtered->getKey());
    }
}
