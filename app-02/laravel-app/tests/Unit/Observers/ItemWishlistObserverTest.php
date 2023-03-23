<?php

namespace Tests\Unit\Observers;

use App\Models\ItemWishlist;
use App\Observers\ItemWishlistObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemWishlistObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $item = ItemWishlist::factory()->make(['uuid' => null]);
        $observer = new ItemWishlistObserver();
        $observer->creating($item);
        $this->assertNotNull($item->uuid);
    }

    /** @test */
    public function it_does_not_change_uuid_when_not_empty()
    {
        $item = ItemWishlist::factory()->make(['uuid' => $uuid = '123456']);
        $observer = new ItemWishlistObserver();
        $observer->creating($item);
        $this->assertSame($uuid, $item->uuid);
    }
}
