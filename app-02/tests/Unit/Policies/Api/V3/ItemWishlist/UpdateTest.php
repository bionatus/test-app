<?php

namespace Tests\Unit\Policies\Api\V3\ItemWishlist;

use App\Models\ItemWishlist;
use App\Models\User;
use App\Models\Wishlist;
use App\Policies\Api\V3\ItemWishlistPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_an_owner_to_update_his_item_wishlist()
    {
        $owner        = User::factory()->create();
        $wishlist     = Wishlist::factory()->usingUser($owner)->create();
        $itemWishlist = ItemWishlist::factory()->usingWishlist($wishlist)->create();

        $policy = new ItemWishlistPolicy();

        $this->assertTrue($policy->update($owner, $itemWishlist));
    }

    /** @test */
    public function it_disallows_another_user_to_update_an_item_wishlist()
    {
        $notOwner     = User::factory()->create();
        $wishlist     = Wishlist::factory()->create();
        $itemWishlist = ItemWishlist::factory()->usingWishlist($wishlist)->create();

        $policy = new ItemWishlistPolicy();

        $this->assertFalse($policy->update($notOwner, $itemWishlist));
    }
}
