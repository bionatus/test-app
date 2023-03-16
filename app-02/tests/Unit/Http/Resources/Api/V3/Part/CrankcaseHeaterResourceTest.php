<?php

namespace Tests\Unit\Http\Resources\Api\V3\Part;

use App\Http\Resources\Api\V3\Part\CrankcaseHeaterResource;
use App\Models\CrankcaseHeater;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class CrankcaseHeaterResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $wattsPower    = $this->faker->text(10);
        $voltage       = $this->faker->text(10);
        $shape         = $this->faker->text(10);
        $minDimension  = $this->faker->text(10);
        $maxDimension  = $this->faker->text(10);
        $probeLength   = $this->faker->text(10);
        $probeDiameter = $this->faker->text(10);
        $leadLength    = $this->faker->text(10);

        $crankcaseHeater = Mockery::mock(CrankcaseHeater::class);
        $crankcaseHeater->shouldReceive('getAttribute')->withArgs(['watts_power'])->once()->andReturn($wattsPower);
        $crankcaseHeater->shouldReceive('getAttribute')->withArgs(['voltage'])->once()->andReturn($voltage);
        $crankcaseHeater->shouldReceive('getAttribute')->withArgs(['shape'])->once()->andReturn($shape);
        $crankcaseHeater->shouldReceive('getAttribute')->withArgs(['min_dimension'])->once()->andReturn($minDimension);
        $crankcaseHeater->shouldReceive('getAttribute')->withArgs(['max_dimension'])->once()->andReturn($maxDimension);
        $crankcaseHeater->shouldReceive('getAttribute')->withArgs(['probe_length'])->once()->andReturn($probeLength);
        $crankcaseHeater->shouldReceive('getAttribute')
            ->withArgs(['probe_diameter'])
            ->once()
            ->andReturn($probeDiameter);
        $crankcaseHeater->shouldReceive('getAttribute')->withArgs(['lead_length'])->once()->andReturn($leadLength);

        $resource = new CrankcaseHeaterResource($crankcaseHeater);

        $response = $resource->resolve();

        $data = [
            'watts_power'    => $wattsPower,
            'voltage'        => $voltage,
            'shape'          => $shape,
            'min_dimension'  => $minDimension,
            'max_dimension'  => $maxDimension,
            'probe_length'   => $probeLength,
            'probe_diameter' => $probeDiameter,
            'lead_length'    => $leadLength,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CrankcaseHeaterResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
