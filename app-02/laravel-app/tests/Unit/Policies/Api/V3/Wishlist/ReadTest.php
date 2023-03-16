<?php

namespace Tests\Unit\Policies\Api\V3\Wishlist;

use App\Models\User;
use App\Models\Wishlist;
use App\Policies\Api\V3\WishlistPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_the_owner_to_read_a_wishlist()
    {
        $owner    = User::factory()->create();
        $wishList = Wishlist::factory()->usingUser($owner)->create();

        $policy = new WishlistPolicy();
        $this->assertTrue($policy->read($owner, $wishList));
    }

    /** @test */
    public function it_disallows_another_user_to_read_a_wishlist()
    {
        $notOwner = User::factory()->create();
        $owner    = User::factory()->create();

        $wishList = Wishlist::factory()->usingUser($owner)->create();

        $policy = new WishlistPolicy();

        $this->assertFalse($policy->read($notOwner, $wishList));
    }
}
