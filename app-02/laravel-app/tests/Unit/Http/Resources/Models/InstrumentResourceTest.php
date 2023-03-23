<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Models\InstrumentResource;
use App\Models\Instrument;
use App\Models\Media;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class InstrumentResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(InstrumentResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $category = Mockery::mock(Instrument::class);
        $category->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name');
        $category->shouldReceive('getFirstMedia')->with(MediaCollectionNames::IMAGES)->once()->andReturnNull();
        $category->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'slug');

        $response = (new InstrumentResource($category))->resolve();

        $data = [
            'id'    => $id,
            'name'  => $name,
            'image' => null,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(InstrumentResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getAttribute')->with('uuid')->once()->andReturn('media uuid');
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('media url');
        $media->shouldReceive('getUrl')->with('thumb')->once()->andReturn('media thumb url');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->once()->andReturnTrue();

        $category = Mockery::mock(Instrument::class);
        $category->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name');
        $category->shouldReceive('getFirstMedia')->with(MediaCollectionNames::IMAGES)->once()->andReturn($media);
        $category->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'slug');

        $response = (new InstrumentResource($category))->resolve();

        $data = [
            'id'    => $id,
            'name'  => $name,
            'image' => new ImageResource($media),
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(InstrumentResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
