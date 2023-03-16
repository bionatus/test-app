<?php

namespace Tests\Unit\Http\Resources\LiveApi\V2\Order;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\LiveApi\V2\Order\ItemResource;
use App\Http\Resources\Models\ImageResource;
use App\Models\Item;
use App\Models\Media;
use App\Models\Part;
use App\Models\Supply;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class ItemResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(ItemResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_part()
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')
            ->withNoArgs()
            ->once()
            ->andReturn($id = '31db2cfd-dd62-475a-8706-569abaf6dcd4');
        $item->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = 'part');
        $item->shouldReceive('isPart')->withNoArgs()->once()->andReturnTrue();
        $item->shouldReceive('isSupply')->withNoArgs()->once()->andReturnFalse();

        $part = Mockery::mock(Part::class);
        $part->shouldReceive('getAttribute')->with('image')->once()->andReturnNull();
        $item->shouldReceive('getAttribute')->with('orderable')->once()->andReturn($part);

        $resource = new ItemResource($item);

        $response = $resource->resolve();

        $data = [
            'id'    => $id,
            'type'  => $type,
            'image' => null,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ItemResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_supply()
    {
        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('media url');
        $media->shouldReceive('getAttribute')->with('uuid')->once()->andReturn('media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->once()->andReturnFalse();

        $supply = Mockery::mock(Supply::class);
        $supply->shouldReceive('getCategoryMedia')->withNoArgs()->once()->andReturn($media);

        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')
            ->withNoArgs()
            ->once()
            ->andReturn($id = '31db2cfd-dd62-475a-8706-569abaf6dcd4');
        $item->shouldReceive('getAttribute')->withArgs(['type'])->once()->andReturn($type = 'supply');
        $item->shouldReceive('isPart')->withNoArgs()->once()->andReturnFalse();
        $item->shouldReceive('isSupply')->withNoArgs()->once()->andReturnTrue();
        $item->shouldReceive('getAttribute')->withArgs(['orderable'])->once()->andReturn($supply);

        $resource = new ItemResource($item);
        $response = $resource->resolve();

        $data = [
            'id'    => $id,
            'type'  => $type,
            'image' => new ImageResource($media),
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ItemResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
