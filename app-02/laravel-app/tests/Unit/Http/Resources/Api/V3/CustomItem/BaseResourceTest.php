<?php

namespace Tests\Unit\Http\Resources\Api\V3\CustomItem;

use App\Http\Resources\Api\V3\CustomItem\BaseResource;
use App\Models\CustomItem;
use App\Models\Item;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')
            ->withNoArgs()
            ->once()
            ->andReturn($id = '464aac0c-bb5c-40f0-9f9c-936ddcaedc30');

        $customItem = Mockery::mock(CustomItem::class);
        $customItem->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'fake custom item');
        $customItem->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $customItem->shouldReceive('getAttribute')->with('creator_type')->once()->andReturn($creator = 'creator type');

        $resource = new BaseResource($customItem);

        $response = $resource->resolve();

        $data = [
            'id'      => $id,
            'name'    => $name,
            'creator' => $creator,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
