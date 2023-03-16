<?php

namespace Tests\Unit\Models\Item\Scopes;

use App\Models\CustomItem;
use App\Models\Item;
use App\Models\Item\Scopes\ByTypes;
use App\Models\Part;
use App\Models\Supply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByTypesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_item_types()
    {
        $parts = Part::factory()->count(2)->create();
        CustomItem::factory()->count(3)->create();
        $supply = Supply::factory()->create();

        $expected = $parts->add($supply);

        $filtered = Item::scoped(new ByTypes([Item::TYPE_PART, Item::TYPE_SUPPLY]))->get();

        $this->assertCount(3, $filtered);
        $filtered->each(function(Item $item) use ($expected) {
            $this->assertSame($expected->shift()->getKey(), $item->getKey());
        });
    }
}
