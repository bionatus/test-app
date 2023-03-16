<?php

namespace Tests\Unit\Observers;

use App\Models\Wishlist;
use App\Observers\WishlistObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $wishlist = Wishlist::factory()->make(['uuid' => null]);
        $observer = new WishlistObserver();
        $observer->creating($wishlist);
        $this->assertNotNull($wishlist->uuid);
    }

    /** @test */
    public function it_does_not_change_uuid_when_not_empty()
    {
        $wishlist = Wishlist::factory()->make(['uuid' => $uuid = '123456']);
        $observer = new WishlistObserver();
        $observer->creating($wishlist);
        $this->assertSame($uuid, $wishlist->uuid);
    }
}
