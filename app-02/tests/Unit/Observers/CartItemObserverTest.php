<?php

namespace Tests\Unit\Observers;

use App\Models\CartItem;
use App\Observers\CartItemObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartItemObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $item = CartItem::factory()->make(['uuid' => null]);

        $observer = new CartItemObserver();

        $observer->creating($item);

        $this->assertNotNull($item->uuid);
    }

    /** @test */
    public function it_does_not_change_uuid_when_not_empty()
    {
        $item = CartItem::factory()->make(['uuid' => $uuid = '123456']);

        $observer = new CartItemObserver();

        $observer->creating($item);

        $this->assertSame($uuid, $item->uuid);
    }
}
