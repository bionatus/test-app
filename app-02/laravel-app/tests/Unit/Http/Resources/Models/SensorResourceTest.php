<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\SensorResource;
use App\Models\Sensor;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class SensorResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $application          = $this->faker->text(25);
        $signalType           = $this->faker->text(25);
        $measurementRange     = $this->faker->text(25);
        $connectionType       = $this->faker->text(25);
        $configuration        = $this->faker->text(25);
        $numberOfWires        = $this->faker->numberBetween();
        $accuracy             = $this->faker->text(25);
        $enclosureRating      = $this->faker->text(25);
        $leadLength           = $this->faker->text(25);
        $operatingTemperature = $this->faker->text(10);

        $sensor = Mockery::mock(Sensor::class);
        $sensor->shouldReceive('getAttribute')->withArgs(['application'])->once()->andReturn($application);
        $sensor->shouldReceive('getAttribute')->withArgs(['signal_type'])->once()->andReturn($signalType);
        $sensor->shouldReceive('getAttribute')->withArgs(['measurement_range'])->once()->andReturn($measurementRange);
        $sensor->shouldReceive('getAttribute')->withArgs(['connection_type'])->once()->andReturn($connectionType);
        $sensor->shouldReceive('getAttribute')->withArgs(['configuration'])->once()->andReturn($configuration);
        $sensor->shouldReceive('getAttribute')->withArgs(['number_of_wires'])->once()->andReturn($numberOfWires);
        $sensor->shouldReceive('getAttribute')->withArgs(['accuracy'])->once()->andReturn($accuracy);
        $sensor->shouldReceive('getAttribute')->withArgs(['enclosure_rating'])->once()->andReturn($enclosureRating);
        $sensor->shouldReceive('getAttribute')->withArgs(['lead_length'])->once()->andReturn($leadLength);
        $sensor->shouldReceive('getAttribute')
            ->withArgs(['operating_temperature'])
            ->once()
            ->andReturn($operatingTemperature);

        $resource = new SensorResource($sensor);

        $response = $resource->resolve();

        $data = [
            'application'           => $application,
            'signal_type'           => $signalType,
            'measurement_range'     => $measurementRange,
            'connection_type'       => $connectionType,
            'configuration'         => $configuration,
            'number_of_wires'       => $numberOfWires,
            'accuracy'              => $accuracy,
            'enclosure_rating'      => $enclosureRating,
            'lead_length'           => $leadLength,
            'operating_temperature' => $operatingTemperature,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SensorResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
