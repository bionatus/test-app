<?php

namespace Tests\Unit\Http\Resources\Api\V3\Oem;

use App\Http\Resources\Api\V3\Oem\Series\BrandResource;
use App\Http\Resources\Api\V3\Oem\SeriesResource;
use App\Http\Resources\Models\Brand\Series\ImageResource;
use App\Models\Brand;
use App\Models\Series;
use Mockery;
use Tests\TestCase;

class SeriesResourceTest extends TestCase
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

        $series = Mockery::mock(Series::class);
        $series->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $series->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'a name');
        $series->shouldReceive('getAttribute')->withArgs(['image'])->once()->andReturn($image = 'an image url');
        $series->shouldReceive('getAttribute')->withArgs(['brand'])->once()->andReturn($brand);

        $resource = new SeriesResource($series);

        $response = $resource->resolve();

        $data = [
            'id'    => $id,
            'name'  => $name,
            'image' => new ImageResource($image),
            'brand' => new BrandResource($brand),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SeriesResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
