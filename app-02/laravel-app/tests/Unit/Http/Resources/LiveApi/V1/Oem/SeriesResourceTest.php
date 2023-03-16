<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\LiveApi\V1\Oem\SeriesResource;
use App\Http\Resources\Models\Brand\Series\ImageResource;
use App\Http\Resources\Models\BrandResource;
use App\Models\Brand;
use App\Models\Series;
use Mockery;
use Tests\TestCase;

class SeriesResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $images = [
            ['id' => '123', 'url' => 'http://image.com'],
        ];

        $brand = Mockery::mock(Brand::class);
        $brand->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($brandId = '77');
        $brand->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($brandName = 'Brand Name');
        $brand->shouldReceive('getAttribute')->withArgs(['logo'])->once()->andReturn($images);

        $series = Mockery::mock(Series::class);
        $series->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($seriesId = 1);
        $series->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($seriesName = 'Series Name');
        $series->shouldReceive('getAttribute')->withArgs(['image'])->once()->andReturn($image = 'an image url');
        $series->shouldReceive('getAttribute')->withArgs(['brand'])->once()->andReturn($brand);

        $resource = new SeriesResource($series);

        $response = $resource->resolve();

        $data = [
            'id'    => $seriesId,
            'name'  => $seriesName,
            'image' => new ImageResource($image),
            'brand' => new BrandResource($brand),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SeriesResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
