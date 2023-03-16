<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\CustomItemResource;
use App\Models\CustomItem;
use App\Models\Item;
use Mockery;
use Tests\TestCase;

class CustomItemResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'uuid');

        $customItem = Mockery::mock(CustomItem::class);
        $customItem->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'name');
        $customItem->shouldReceive('getAttribute')->withArgs(['item'])->once()->andReturn($item);
        $customItem->shouldReceive('getAttribute')->withArgs(['creator_type'])->once()->andReturnNull();

        $resource = new CustomItemResource($customItem);

        $response = $resource->resolve();

        $data = [
            'id'      => $id,
            'name'    => $name,
            'creator' => null,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CustomItemResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'uuid');

        $customItem = Mockery::mock(CustomItem::class);
        $customItem->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'name');
        $customItem->shouldReceive('getAttribute')->withArgs(['item'])->once()->andReturn($item);
        $customItem->shouldReceive('getAttribute')->withArgs(['creator_type'])->once()->andReturn($creator = 'creator');

        $resource = new CustomItemResource($customItem);

        $response = $resource->resolve();

        $data = [
            'id'      => $id,
            'name'    => $name,
            'creator' => $creator,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CustomItemResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
