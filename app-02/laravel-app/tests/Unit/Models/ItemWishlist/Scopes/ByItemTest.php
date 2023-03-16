<?php

namespace Tests\Unit\Models\ItemWishlist\Scopes;

use App\Models\Item;
use App\Models\ItemWishlist;
use App\Models\ItemWishlist\Scopes\ByItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByItemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_item()
    {
        $item         = Item::factory()->create();
        $itemWishlist = ItemWishlist::factory()->usingItem($item)->create();
        ItemWishlist::factory()->create();

        $filtered = ItemWishlist::scoped(new ByItem($item))->first();

        $this->assertInstanceOf(ItemWishlist::class, $filtered);
        $this->assertSame($itemWishlist->getKey(), $filtered->getKey());
    }
}
