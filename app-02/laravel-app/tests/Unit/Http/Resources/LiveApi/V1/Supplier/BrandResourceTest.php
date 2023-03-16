<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Supplier;

use App\Http\Resources\LiveApi\V1\Supplier\BrandResource;
use App\Http\Resources\Models\Brand\ImageResource;
use App\Models\Brand;
use Mockery;
use Tests\TestCase;

class BrandResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $id     = '55';
        $name   = 'a name';
        $images = [];
        $brand  = Mockery::mock(Brand::class);
        $brand->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $brand->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name);
        $brand->shouldReceive('getAttribute')->withArgs(['logo'])->once()->andReturn($images);

        $resource = new BrandResource($brand);
        $response = $resource->resolve();

        $data = [
            'id'    => $id,
            'name'  => $name,
            'image' => new ImageResource($images),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BrandResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
