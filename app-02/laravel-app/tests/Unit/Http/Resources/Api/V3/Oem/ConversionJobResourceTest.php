<?php

namespace Tests\Unit\Http\Resources\Api\V3\Oem;

use App\Http\Resources\Api\V3\Oem\ConversionJobResource;
use App\Models\ConversionJob;
use Mockery;
use Tests\TestCase;

class ConversionJobResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $conversionJob = Mockery::mock(ConversionJob::class);
        $conversionJob->shouldReceive('getAttribute')->with('control')->once()->andReturn($control = 'control');
        $conversionJob->shouldReceive('getAttribute')->with('standard')->once()->andReturn($standard = 'standard');
        $conversionJob->shouldReceive('getAttribute')->with('optional')->once()->andReturn($optional = 'optional');
        $conversionJob->shouldReceive('getAttribute')->with('retrofit')->once()->andReturn($retrofit = 'retrofit');
        $conversionJob->shouldReceive('getAttribute')->with('image')->once()->andReturnNull();

        $resource = new ConversionJobResource($conversionJob);

        $response = $resource->resolve();

        $data = [
            'control'  => $control,
            'standard' => $standard,
            'optional' => $optional,
            'retrofit' => $retrofit,
            'image'    => '',
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ConversionJobResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
