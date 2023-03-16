<?php

namespace Tests\Unit\Http\Resources\Api\V4\Order\ItemOrder\ExtraItem;

use App\Http\Resources\Api\V4\Order\ItemOrder\ExtraItem\ItemResource;
use App\Http\Resources\Models\CustomItemResource;
use App\Http\Resources\Models\SupplyResource;
use App\Models\CustomItem;
use App\Models\Item;
use App\Models\Supply;
use App\Models\User;
use Mockery;
use Tests\TestCase;

class ItemResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields_with_supply()
    {
        $supply = Mockery::mock(Supply::class);
        $item   = Mockery::mock(Item::class);

        $item->shouldReceive('getRouteKey')->withNoArgs()->twice()->andReturn($itemId = 'item id');
        $item->shouldReceive('getAttribute')->with('type')->once()->andReturn($itemType = Item::TYPE_SUPPLY);
        $item->shouldReceive('getAttribute')->with('orderable')->once()->andReturn($supply);
        $item->shouldReceive('isSupply')->withNoArgs()->once()->andReturnTrue();
        $item->shouldReceive('isCustomItem')->withNoArgs()->once()->andReturnFalse();

        $supply->shouldReceive('getCategoryMedia')->withNoArgs()->once()->andReturnNull();
        $supply->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $supply->shouldReceive('getAttribute')->with('name')->once()->andReturn('supply name');
        $supply->shouldReceive('getAttribute')->with('internal_name')->once()->andReturn('internal name');
        $supply->shouldReceive('getAttribute')->with('sort')->once()->andReturnNull();

        $resource = new ItemResource($item);
        $response = $resource->resolve();

        $data = [
            'id'   => $itemId,
            'type' => $itemType,
            'info' => new SupplyResource($supply),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ItemResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_custom_item()
    {
        $customItem = Mockery::mock(CustomItem::class);
        $item       = Mockery::mock(Item::class);

        $item->shouldReceive('getRouteKey')->withNoArgs()->twice()->andReturn($itemId = 'item id');
        $item->shouldReceive('getAttribute')->with('type')->once()->andReturn($itemType = Item::TYPE_SUPPLY);
        $item->shouldReceive('getAttribute')->with('orderable')->once()->andReturn($customItem);
        $item->shouldReceive('isSupply')->withNoArgs()->once()->andReturnFalse();
        $item->shouldReceive('isCustomItem')->withNoArgs()->once()->andReturnTrue();

        $customItem->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $customItem->shouldReceive('getAttribute')->with('name')->once()->andReturn('custom item name');
        $customItem->shouldReceive('getAttribute')->with('creator_type')->andReturn(User::MORPH_ALIAS);

        $resource = new ItemResource($item);
        $response = $resource->resolve();

        $data = [
            'id'   => $itemId,
            'type' => $itemType,
            'info' => new CustomItemResource($customItem),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ItemResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
