<?php

namespace Tests\Unit\Models;

use App\Models\CustomItem;
use App\Models\Item;
use App\Models\Part;
use App\Models\Supply;
use Illuminate\Support\Str;

class ItemTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Item::tableName(), [
            'id',
            'uuid',
            'type',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $item = Item::factory()->create(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($item->uuid, $item->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $item = Item::factory()->make(['uuid' => null]);
        $item->save();

        $this->assertNotNull($item->uuid);
    }

    /** @test */
    public function it_knows_if_is_a_part()
    {
        $itemPart   = Part::factory()->create()->item;
        $itemSupply = Supply::factory()->create()->item;
        $itemCustom = CustomItem::factory()->create()->item;

        $this->assertTrue($itemPart->isPart());
        $this->assertFalse($itemSupply->isPart());
        $this->assertFalse($itemCustom->isPart());
    }

    /** @test */
    public function it_knows_if_is_a_supply()
    {
        $itemPart   = Part::factory()->create()->item;
        $itemSupply = Supply::factory()->create()->item;
        $itemCustom = CustomItem::factory()->create()->item;

        $this->assertFalse($itemPart->isSupply());
        $this->assertTrue($itemSupply->isSupply());
        $this->assertFalse($itemCustom->isSupply());
    }

    /** @test */
    public function it_knows_if_is_a_custom_item()
    {
        $itemPart   = Part::factory()->create()->item;
        $itemSupply = Supply::factory()->create()->item;
        $itemCustom = CustomItem::factory()->create()->item;

        $this->assertFalse($itemPart->isCustomItem());
        $this->assertFalse($itemSupply->isCustomItem());
        $this->assertTrue($itemCustom->isCustomItem());
    }
}
