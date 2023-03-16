<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\LiveApi\V1\Oem\BaseResource;
use App\Http\Resources\LiveApi\V1\Oem\SeriesResource;
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
        $brand->shouldReceive('getAttribute')->with('logo')->once()->andReturn([]);

        $series = Mockery::mock(Series::class);
        $series->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(2);
        $series->shouldReceive('getAttribute')->with('name')->once()->andReturn('series name');
        $series->shouldReceive('getAttribute')->with('image')->once()->andReturnNull();
        $series->shouldReceive('getAttribute')->with('brand')->once()->andReturn($brand);

        $oem = Mockery::mock(Oem::class);
        $oem->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'id');
        $oem->shouldReceive('getAttribute')->with('model')->once()->andReturn($model = 'a model');
        $oem->shouldReceive('getAttribute')->with('model_notes')->once()->andReturn($modelNote = 'a model note');
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
            'model_notes'            => $modelNote,
            'functional_parts_count' => $parts,
            'manuals_count'          => $manualsCount,
            'logo'                   => $logo,
            'image'                  => $image,
            'call_group_tags'        => $callGroupTags,
            'calling_groups'         => $callingGroups,
            'series'                 => new SeriesResource($series),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
