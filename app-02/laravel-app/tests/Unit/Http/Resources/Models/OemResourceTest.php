<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\OemResource;
use App\Models\Oem;
use Mockery;
use Tests\TestCase;

class OemResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $oem = Mockery::mock(Oem::class);
        $oem->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'id');
        $oem->shouldReceive('getAttribute')->with('model')->once()->andReturn($model = 'a model');
        $oem->shouldReceive('getAttribute')->with('model_notes')->once()->andReturn($model_note = 'a model note');
        $oem->shouldReceive('getAttribute')->with('logo')->once()->andReturn($logo = 'https://fake-logo');
        $oem->shouldReceive('getAttribute')->with('unit_image')->once()->andReturn($image = 'https://fake-image');
        $oem->shouldReceive('getAttribute')
            ->with('calling_groups')
            ->once()
            ->andReturn($callingGroups = 'calling_groups');
        $oem->shouldReceive('getAttribute')
            ->with('call_group_tags')
            ->once()
            ->andReturn($callGroupTags = 'call_group_tags');
        $oem->shouldReceive('functionalPartsCount')->withNoArgs()->once()->andReturn($parts = 3);
        $oem->shouldReceive('manualsCount')->withNoArgs()->once()->andReturn($manualsCount = 5);

        $resource = new OemResource($oem);

        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
            'model'                  => $model,
            'model_notes'            => $model_note,
            'functional_parts_count' => $parts,
            'manuals_count'          => $manualsCount,
            'logo'                   => $logo,
            'image'                  => $image,
            'calling_groups'         => $callingGroups,
            'call_group_tags'        => $callGroupTags,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(OemResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
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

        $resource = new OemResource($oem);

        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
            'model'                  => $model,
            'model_notes'            => null,
            'functional_parts_count' => $parts,
            'manuals_count'          => $manualsCount,
            'logo'                   => $logo,
            'image'                  => null,
            'calling_groups'         => null,
            'call_group_tags'        => null,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(OemResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
