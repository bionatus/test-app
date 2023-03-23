<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Brand\Series;

use App\Http\Resources\LiveApi\V1\Brand\Series\BaseResource;
use App\Http\Resources\Models\Brand\Series\ImageResource;
use App\Models\Series;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $series = Mockery::mock(Series::class);
        $series->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $series->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'a name');
        $series->shouldReceive('getAttribute')->withArgs(['image'])->once()->andReturn($image = 'an image url');

        $resource = new BaseResource($series);

        $response = $resource->resolve();

        $data = [
            'id'    => $id,
            'name'  => $name,
            'image' => new ImageResource($image),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_when_image_is_null()
    {
        $series = Mockery::mock(Series::class);
        $series->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $series->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'a name');
        $series->shouldReceive('getAttribute')->withArgs(['image'])->once()->andReturn(null);

        $resource = new BaseResource($series);

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
