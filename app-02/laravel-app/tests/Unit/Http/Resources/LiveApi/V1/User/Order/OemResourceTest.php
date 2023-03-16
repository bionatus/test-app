<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\User\Order;

use App\Http\Resources\LiveApi\V1\Oem\SeriesResource;
use App\Http\Resources\LiveApi\V1\User\Order\OemResource;
use App\Models\Brand;
use App\Models\Oem;
use App\Models\Series;
use Mockery;
use Tests\TestCase;

class OemResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $brand = Mockery::mock(Brand::class);
        $brand->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('brand id');
        $brand->shouldReceive('getAttribute')->with('name')->once()->andReturn('brand name');
        $brand->shouldReceive('getAttribute')->with('logo')->once()->andReturn([]);

        $series = Mockery::mock(Series::class);
        $series->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(2);
        $series->shouldReceive('getAttribute')->with('name')->once()->andReturn('series name');
        $series->shouldReceive('getAttribute')->with('image')->once()->andReturnNull();
        $series->shouldReceive('getAttribute')->with('brand')->once()->andReturn($brand);

        $oem = Mockery::mock(Oem::class);
        $oem->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'id');
        $oem->shouldReceive('getAttribute')->with('model')->once()->andReturn($model = 'a model');
        $oem->shouldReceive('getAttribute')->with('model_notes')->once()->andReturnNull();
        $oem->shouldReceive('getAttribute')->with('logo')->once()->andReturn($logo = 'https://fake-logo');
        $oem->shouldReceive('getAttribute')->with('unit_image')->once()->andReturnNull();
        $oem->shouldReceive('getAttribute')->with('series')->once()->andReturn($series);

        $resource = new OemResource($oem);

        $response = $resource->resolve();

        $data = [
            'id'          => $id,
            'model'       => $model,
            'model_notes' => null,
            'logo'        => $logo,
            'image'       => null,
            'series'      => new SeriesResource($series),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(OemResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $brand = Mockery::mock(Brand::class);
        $brand->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('brand id');
        $brand->shouldReceive('getAttribute')->with('name')->once()->andReturn('brand name');
        $brand->shouldReceive('getAttribute')->with('logo')->once()->andReturn([]);

        $series = Mockery::mock(Series::class);
        $series->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(2);
        $series->shouldReceive('getAttribute')->with('name')->once()->andReturn('series name');
        $series->shouldReceive('getAttribute')->with('image')->once()->andReturnNull();
        $series->shouldReceive('getAttribute')->with('brand')->once()->andReturn($brand);

        $oem = Mockery::mock(Oem::class);
        $oem->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'id');
        $oem->shouldReceive('getAttribute')->with('model')->once()->andReturn($model = 'a model');
        $oem->shouldReceive('getAttribute')->with('model_notes')->once()->andReturn($model_note = 'a model note');
        $oem->shouldReceive('getAttribute')->with('logo')->once()->andReturn($logo = 'https://fake-logo');
        $oem->shouldReceive('getAttribute')->with('unit_image')->once()->andReturn($image = 'https://fake-image');
        $oem->shouldReceive('getAttribute')->with('series')->once()->andReturn($series);

        $resource = new OemResource($oem);

        $response = $resource->resolve();

        $data = [
            'id'          => $id,
            'model'       => $model,
            'model_notes' => $model_note,
            'logo'        => $logo,
            'image'       => $image,
            'series'      => new SeriesResource($series),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(OemResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
