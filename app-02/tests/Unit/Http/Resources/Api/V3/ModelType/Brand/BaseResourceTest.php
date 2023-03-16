<?php

namespace Tests\Unit\Http\Resources\Api\V3\ModelType\Brand;

use App\Http\Resources\Api\V3\ModelType\Brand\BaseResource;
use App\Http\Resources\Api\V3\ModelType\Brand\ImageResource;
use App\Models\Brand;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $id     = '77';
        $name   = 'Brand Name';
        $images = [
            ['id' => '123', 'url' => 'http://image.com'],
        ];

        $brand = Mockery::mock(Brand::class);
        $brand->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $brand->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name);
        $brand->shouldReceive('getAttribute')->withArgs(['logo'])->twice()->andReturn($images);

        $resource = new BaseResource($brand);
        $response = $resource->resolve();

        $data = [
            'id'    => $id,
            'name'  => $name,
            'image' => new ImageResource($images),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_when_there_is_no_image()
    {
        $id     = '77';
        $name   = 'Brand Name';
        $images = [];

        $brand = Mockery::mock(Brand::class);
        $brand->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $brand->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name);
        $brand->shouldReceive('getAttribute')->withArgs(['logo'])->twice()->andReturn($images);

        $resource = new BaseResource($brand);
        $response = $resource->resolve();

        $data = [
            'id'    => $id,
            'name'  => $name,
            'image' => null,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
