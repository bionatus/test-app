<?php

namespace Tests\Unit\Observers;

use App\Models\Item;
use App\Observers\ItemObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $item = Item::factory()->make(['uuid' => null]);

        $observer = new ItemObserver();

        $observer->creating($item);

        $this->assertNotNull($item->uuid);
    }

    /** @test */
    public function it_does_not_change_uuid_when_not_empty()
    {
        $item = Item::factory()->make(['uuid' => $uuid = '123456']);

        $observer = new ItemObserver();

        $observer->creating($item);

        $this->assertSame($uuid, $item->uuid);
    }
}
