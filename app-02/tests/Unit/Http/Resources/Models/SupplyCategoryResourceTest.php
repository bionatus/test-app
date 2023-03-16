<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Models\SupplyCategoryResource;
use App\Models\Media;
use App\Models\SupplyCategory;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class SupplyCategoryResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $subcategory   = Mockery::mock(SupplyCategory::class);
        $subcategories = Collection::make([$subcategory]);

        $id   = 'fake-category';
        $name = 'Fake category';

        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn($url = 'media url');
        $media->shouldReceive('getUrl')->withArgs(['thumb'])->once()->andReturn($thumbUrl = 'media thumb url');
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->once()->andReturn($uuid = 'media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->once()->andReturn(true);

        $category = Mockery::mock(SupplyCategory::class);
        $category->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $category->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name);
        $category->shouldReceive('getAttribute')->withArgs(['children'])->andReturn($subcategories);
        $category->shouldReceive('getFirstMedia')->withArgs([MediaCollectionNames::IMAGES])->once()->andReturn($media);

        $resource = new SupplyCategoryResource($category);

        $response = $resource->resolve();

        $data = [
            'id'    => $id,
            'name'  => $name,
            'image' => new ImageResource($media),
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplyCategoryResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_without_media()
    {
        $subcategory   = Mockery::mock(SupplyCategory::class);
        $subcategories = Collection::make([$subcategory]);

        $id   = 'fake-category';
        $name = 'Fake category';

        $category = Mockery::mock(SupplyCategory::class);
        $category->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $category->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name);
        $category->shouldReceive('getAttribute')->withArgs(['children'])->andReturn($subcategories);
        $category->shouldReceive('getFirstMedia')->withArgs([MediaCollectionNames::IMAGES])->once()->andReturnNull();

        $resource = new SupplyCategoryResource($category);

        $response = $resource->resolve();

        $data = [
            'id'    => $id,
            'name'  => $name,
            'image' => null,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SupplyCategoryResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
