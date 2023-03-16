<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Oem;

use App\Http\Resources\Api\V3\Account\Oem\Series\BrandResource;
use App\Http\Resources\Api\V3\Account\Oem\SeriesResource;
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
        $brand = Mockery::mock(Brand::class);
        $brand->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('77');
        $brand->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn('Brand Name');
        $brand->shouldReceive('getAttribute')->withArgs(['logo'])->twice()->andReturn([
            ['id' => '123', 'url' => 'http://image.com'],
        ]);

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
