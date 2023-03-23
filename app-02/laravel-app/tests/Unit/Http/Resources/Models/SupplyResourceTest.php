<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Models\SupplyResource;
use App\Models\Item;
use App\Models\Media;
use App\Models\Supply;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class SupplyResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(SupplyResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'route key');

        $supply = Mockery::mock(Supply::class);
        $supply->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $supply->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name');
        $supply->shouldReceive('getAttribute')
            ->withArgs(['internal_name'])
            ->once()
            ->andReturn($internalName = 'internal name');
        $supply->shouldReceive('getAttribute')->with('sort')->once()->andReturnNull();
        $supply->shouldReceive('getCategoryMedia')->withNoArgs()->once()->andReturnNull();

        $response = (new SupplyResource($supply))->resolve();

        $data = [
            'id'            => $id,
            'name'          => $name,
            'internal_name' => $internalName,
            'sort'          => null,
            'image'         => null,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplyResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'route key');

        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->once()->andReturn('media uuid');
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('media url');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->once()->andReturnFalse();

        $supply = Mockery::mock(Supply::class);
        $supply->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $supply->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name');
        $supply->shouldReceive('getAttribute')
            ->withArgs(['internal_name'])
            ->once()
            ->andReturn($internalName = 'internal name');
        $supply->shouldReceive('getAttribute')->with('sort')->once()->andReturn($sort = 1);
        $supply->shouldReceive('getCategoryMedia')->withNoArgs()->once()->andReturn($media);

        $response = (new SupplyResource($supply))->resolve();

        $data = [
            'id'            => $id,
            'name'          => $name,
            'internal_name' => $internalName,
            'sort'          => $sort,
            'image'         => new ImageResource($media),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplyResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
