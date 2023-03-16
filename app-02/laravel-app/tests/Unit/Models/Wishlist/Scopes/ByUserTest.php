<?php

namespace Tests\Unit\Models\Wishlist\Scopes;

use App\Models\User;
use App\Models\Wishlist;
use App\Models\Wishlist\Scopes\ByUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByUserTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_filters_by_user_id()
    {
        $wishlist = Wishlist::factory()->usingUser($this->user)->create();
        Wishlist::factory()->count(2)->create();

        $wishlistFound = Wishlist::scoped(new ByUser($this->user))->first();

        $this->assertInstanceOf(Wishlist::class, $wishlist);
        $this->assertEquals($wishlist->getKey(), $wishlistFound->getKey());
    }
}
