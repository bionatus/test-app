<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\Brand\Series\ImageResource;
use App\Http\Resources\Models\SeriesResource;
use App\Models\Series;
use Mockery;
use Tests\TestCase;

class SeriesResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $series = Mockery::mock(Series::class);
        $series->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $series->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'a name');
        $series->shouldReceive('getAttribute')->withArgs(['image'])->once()->andReturn($image = 'an image url');

        $resource = new SeriesResource($series);

        $response = $resource->resolve();

        $data = [
            'id'    => $id,
            'name'  => $name,
            'image' => new ImageResource($image),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SeriesResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
