<?php

namespace Tests\Unit\Models\ItemOrder\Scopes;

use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\ItemOrder\Scopes\IsPart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsPartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_part_type()
    {
        $item      = Item::factory()->part()->create();
        $itemOrder = ItemOrder::factory()->usingItem($item)->createQuietly();
        ItemOrder::factory()->count(10)->createQuietly();

        $filtered = ItemOrder::query()->scoped(new IsPart())->get();
        $this->assertCount(1, $filtered);
        $this->assertEquals($itemOrder->getKey(), $filtered->first()->getkey());
    }
}
