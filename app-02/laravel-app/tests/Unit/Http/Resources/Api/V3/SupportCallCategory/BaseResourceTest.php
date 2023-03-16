<?php

namespace Tests\Unit\Http\Resources\Api\V3\SupportCallCategory;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V3\SupportCallCategory\BaseResource;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Models\InstrumentResource;
use App\Models\Instrument;
use App\Models\Media;
use App\Models\SupportCallCategory;
use Illuminate\Support\Collection;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(BaseResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $category = Mockery::mock(SupportCallCategory::class);
        $category->shouldReceive('getAttribute')->with('children_exists')->once()->andReturnFalse();
        $category->shouldReceive('getAttribute')->with('description')->once()->andReturnNull();
        $category->shouldReceive('getAttribute')
            ->with('instruments')
            ->once()
            ->andReturn($instruments = Collection::empty());
        $category->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name');
        $category->shouldReceive('getAttribute')->with('phone')->once()->andReturn($phone = 'phone');
        $category->shouldReceive('getFirstMedia')->with(MediaCollectionNames::IMAGES)->once()->andReturnNull();
        $category->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'slug');

        $response = (new BaseResource($category))->resolve();

        $data = [
            'id'              => $id,
            'name'            => $name,
            'description'     => null,
            'phone'           => $phone,
            'image'           => null,
            'instruments'     => InstrumentResource::collection($instruments),
            'has_descendants' => false,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $instrument = Mockery::mock(Instrument::class);
        $instrument->shouldReceive('getAttribute')->with('name')->once()->andReturn('instrument name');
        $instrument->shouldReceive('getFirstMedia')->with(MediaCollectionNames::IMAGES)->once()->andReturnNull();
        $instrument->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('instrument slug');

        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getAttribute')->with('uuid')->once()->andReturn('media uuid');
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('media url');
        $media->shouldReceive('getUrl')->with('thumb')->once()->andReturn('media thumb url');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->once()->andReturnTrue();

        $category = Mockery::mock(SupportCallCategory::class);
        $category->shouldReceive('getAttribute')->with('children_exists')->once()->andReturnTrue();
        $category->shouldReceive('getAttribute')->with('description')->once()->andReturn($description = 'description');
        $category->shouldReceive('getAttribute')
            ->with('instruments')
            ->once()
            ->andReturn($instruments = Collection::make([$instrument]));
        $category->shouldReceive('getAttribute')->with('name')->once()->andReturn($name = 'name');
        $category->shouldReceive('getAttribute')->with('phone')->once()->andReturn($phone = 'phone');
        $category->shouldReceive('getFirstMedia')->with(MediaCollectionNames::IMAGES)->once()->andReturn($media);
        $category->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'slug');

        $response = (new BaseResource($category))->resolve();

        $data = [
            'id'              => $id,
            'name'            => $name,
            'description'     => $description,
            'phone'           => $phone,
            'image'           => new ImageResource($media),
            'instruments'     => InstrumentResource::collection($instruments),
            'has_descendants' => true,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
