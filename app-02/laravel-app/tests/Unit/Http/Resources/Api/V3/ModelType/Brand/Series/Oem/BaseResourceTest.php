<?php

namespace Tests\Unit\Http\Resources\Api\V3\ModelType\Brand\Series\Oem;

use App\Http\Resources\Api\V3\ModelType\Brand\Series\Oem\BaseResource;
use App\Models\Oem;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
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
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
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

        $resource = new BaseResource($oem);

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
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
