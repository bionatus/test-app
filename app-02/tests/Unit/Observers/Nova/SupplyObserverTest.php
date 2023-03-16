<?php

namespace Tests\Unit\Observers\Nova;

use App\Models\Item;
use App\Models\Supply;
use App\Nova\Observers\SupplyObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplyObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_related_item_when_creating()
    {
        $supply = Supply::factory()->make(['id' => null]);

        $observer = new SupplyObserver();
        $observer->creating($supply);

        $this->assertDatabaseHas(Item::tableName(), ['id' => $supply->getKey()]);
    }

    /** @test */
    public function it_fills_id_when_creating()
    {
        $supply = Supply::factory()->make(['id' => null]);

        $observer = new SupplyObserver();
        $observer->creating($supply);

        $this->assertNotNull($supply->id);
    }

    /** @test */
    public function it_deletes_the_item_related_when_deleted()
    {
        $supply = Supply::factory()->create();
        $item   = $supply->item;

        $observer = new SupplyObserver();
        $observer->deleted($supply);

        $this->assertDatabaseMissing(Item::tableName(), [
            'id' => $item->id,
        ]);
    }
}
