<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\Oem;

use App\Http\Resources\Api\V3\Account\Oem\BaseResource;
use App\Http\Resources\Api\V3\Account\Oem\SeriesResource;
use App\Models\Brand;
use App\Models\Oem;
use App\Models\Series;
use Mockery;
use Tests\TestCase;

/**
 * @property Oem $resource
 */
class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $brand = Mockery::mock(Brand::class);
        $brand->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('brand id');
        $brand->shouldReceive('getAttribute')->with('name')->once()->andReturn('brand name');
        $brand->shouldReceive('getAttribute')->with('logo')->twice()->andReturn([]);

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
        $oem->shouldReceive('getAttribute')
            ->with('call_group_tags')
            ->once()
            ->andReturn($callGroupTags = 'call group tag fake');
        $oem->shouldReceive('getAttribute')
            ->with('calling_groups')
            ->once()
            ->andReturn($callingGroups = 'calling groups fake');
        $oem->shouldReceive('functionalPartsCount')->withNoArgs()->once()->andReturn($parts = 3);
        $oem->shouldReceive('manualsCount')->withNoArgs()->once()->andReturn($manualsCount = 5);
        $oem->shouldReceive('getAttribute')->with('series')->once()->andReturn($series);

        $resource = new BaseResource($oem);

        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
            'model'                  => $model,
            'model_notes'            => $model_note,
            'logo'                   => $logo,
            'image'                  => $image,
            'call_group_tags'        => $callGroupTags,
            'calling_groups'         => $callingGroups,
            'functional_parts_count' => $parts,
            'manuals_count'          => $manualsCount,
            'series'                 => new SeriesResource($series),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $brand = Mockery::mock(Brand::class);
        $brand->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('brand id');
        $brand->shouldReceive('getAttribute')->with('name')->once()->andReturn('brand name');
        $brand->shouldReceive('getAttribute')->with('logo')->twice()->andReturn([]);

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
        $oem->shouldReceive('getAttribute')->with('calling_groups')->once()->andReturnNull();
        $oem->shouldReceive('getAttribute')->with('call_group_tags')->once()->andReturnNull();
        $oem->shouldReceive('functionalPartsCount')->withNoArgs()->once()->andReturn($parts = 3);
        $oem->shouldReceive('manualsCount')->withNoArgs()->once()->andReturn($manualsCount = 5);
        $oem->shouldReceive('getAttribute')->with('series')->once()->andReturn($series);

        $resource = new BaseResource($oem);

        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
            'model'                  => $model,
            'model_notes'            => null,
            'logo'                   => $logo,
            'image'                  => null,
            'calling_groups'         => null,
            'call_group_tags'        => null,
            'functional_parts_count' => $parts,
            'manuals_count'          => $manualsCount,
            'series'                 => new SeriesResource($series),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
