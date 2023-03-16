<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Part\RecentlyViewed;

use App\Http\Resources\Api\V3\Account\Part\RecentlyViewed\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\Part\ImageResource;
use App\Models\Item;
use App\Models\Other;
use App\Models\Part;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'id');

        $part = Mockery::mock(Part::class);
        $part->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $part->shouldReceive('getAttribute')->with('number')->once()->andReturn($number = '54-845Num');
        $part->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = 'air_filter');
        $part->shouldReceive('getAttribute')->with('subtype')->once()->andReturn($subtype = 'specific');
        $part->shouldReceive('getAttribute')->with('brand')->once()->andReturn($brand = 'a brand');
        $part->shouldReceive('getAttribute')->with('image')->once()->andReturn($image = 'https://image.com');
        $part->shouldReceive('getAttribute')->with('subcategory')->once()->andReturn($subcategory = 'fake subcategory');
        $part->shouldReceive('isOther')->withNoArgs()->once()->andReturnFalse();

        $resource = new BaseResource($part);

        $response = $resource->resolve();
        $data     = [
            'id'          => $id,
            'number'      => $number,
            'type'        => $type,
            'subtype'     => $subtype,
            'description' => null,
            'brand'       => $brand,
            'image'       => new ImageResource($image),
            'subcategory' => $subcategory,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_other_type()
    {
        $item = Mockery::mock(Item::class);
        $item->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'id');

        $other = Mockery::mock(Other::class);
        $other->shouldReceive('getAttribute')
            ->withArgs(['description'])
            ->once()
            ->andReturn($description = 'fake description');

        $part = Mockery::mock(Part::class);
        $part->shouldReceive('getAttribute')->with('item')->once()->andReturn($item);
        $part->shouldReceive('getAttribute')->with('number')->once()->andReturn($number = '54-845Num');
        $part->shouldReceive('getAttribute')->with('type')->once()->andReturn($type = 'air_filter');
        $part->shouldReceive('getAttribute')->with('subtype')->once()->andReturn($subtype = 'specific');
        $part->shouldReceive('getAttribute')->with('brand')->once()->andReturn($brand = 'a brand');
        $part->shouldReceive('getAttribute')->with('image')->once()->andReturn($image = 'https://image.com');
        $part->shouldReceive('getAttribute')->with('subcategory')->once()->andReturn($subcategory = 'fake subcategory');
        $part->shouldReceive('getAttribute')->withArgs(['detail'])->twice()->andReturn($other);
        $part->shouldReceive('isOther')->withNoArgs()->once()->andReturnTrue();

        $resource = new BaseResource($part);

        $response = $resource->resolve();

        $data = [
            'id'          => $id,
            'number'      => $number,
            'type'        => $type,
            'subtype'     => $subtype,
            'description' => $description,
            'brand'       => $brand,
            'image'       => new ImageResource($image),
            'subcategory' => $subcategory,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
