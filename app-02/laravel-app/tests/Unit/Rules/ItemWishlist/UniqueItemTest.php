<?php

namespace Tests\Unit\Rules\ItemWishlist;

use App\Models\Item;
use App\Models\ItemWishlist;
use App\Models\Wishlist;
use App\Rules\ItemWishlist\UniqueItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniqueItemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_false_if_value_already_exists_on_wishlist()
    {
        $itemWishlist = ItemWishlist::factory()->create();
        $item         = $itemWishlist->item;

        $rule = new UniqueItem($itemWishlist->wishlist);

        $this->assertFalse($rule->passes('', $item->getRouteKey()));
    }

    /** @test */
    public function it_returns_true_if_item_is_not_in_the_wishlist()
    {
        $wishlist = Wishlist::factory()->create();
        $item     = Item::factory()->create();

        $rule = new UniqueItem($wishlist);

        $this->assertTrue($rule->passes('', $item->getRouteKey()));
    }

    /** @test */
    public function it_has_specific_error_message()
    {
        $itemWishlist = ItemWishlist::factory()->create();

        $expectedMessage = 'This :attribute already exists on the wishlist';
        $rule            = new UniqueItem($itemWishlist->wishlist);

        $this->assertEquals($expectedMessage, $rule->message());
    }
}
