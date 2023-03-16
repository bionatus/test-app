<?php

namespace Tests\Unit\Http\Resources\Api\V3\Part;

use App\Http\Resources\Api\V3\Part\IgniterResource;
use App\Models\Igniter;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class IgniterResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $application                = $this->faker->text(50);
        $gasType                    = $this->faker->text(10);
        $voltage                    = $this->faker->text(10);
        $terminalType               = $this->faker->text(10);
        $mounting                   = $this->faker->text(100);
        $tipStyle                   = $this->faker->text(25);
        $ceramicBlock               = $this->faker->text(10);
        $pilotBtu                   = $this->faker->text(10);
        $orificeDiameter            = $this->faker->text(10);
        $pilotTubeLength            = $this->faker->text(10);
        $leadLength                 = $this->faker->text(10);
        $sensorType                 = $this->faker->text(25);
        $steadyCurrent              = $this->faker->text(25);
        $tempRating                 = $this->faker->text(10);
        $timeToTemp                 = $this->faker->text(50);
        $amperage                   = $this->faker->text(50);
        $coldResistance             = $this->faker->text(50);
        $maxCurrent                 = $this->faker->text(25);
        $compressionFittingDiameter = $this->faker->text(25);
        $probeLength                = $this->faker->text(25);
        $rodAngle                   = $this->faker->text(25);
        $length                     = $this->faker->text(25);
        $height                     = $this->faker->text(25);
        $width                      = $this->faker->text(25);

        $igniter = Mockery::mock(Igniter::class);
        $igniter->shouldReceive('getAttribute')->withArgs(['application'])->once()->andReturn($application);
        $igniter->shouldReceive('getAttribute')->withArgs(['gas_type'])->once()->andReturn($gasType);
        $igniter->shouldReceive('getAttribute')->withArgs(['voltage'])->once()->andReturn($voltage);
        $igniter->shouldReceive('getAttribute')->withArgs(['terminal_type'])->once()->andReturn($terminalType);
        $igniter->shouldReceive('getAttribute')->withArgs(['mounting'])->once()->andReturn($mounting);
        $igniter->shouldReceive('getAttribute')->withArgs(['tip_style'])->once()->andReturn($tipStyle);
        $igniter->shouldReceive('getAttribute')->withArgs(['ceramic_block'])->once()->andReturn($ceramicBlock);
        $igniter->shouldReceive('getAttribute')->withArgs(['pilot_btu'])->once()->andReturn($pilotBtu);
        $igniter->shouldReceive('getAttribute')->withArgs(['orifice_diameter'])->once()->andReturn($orificeDiameter);
        $igniter->shouldReceive('getAttribute')->withArgs(['pilot_tube_length'])->once()->andReturn($pilotTubeLength);
        $igniter->shouldReceive('getAttribute')->withArgs(['lead_length'])->once()->andReturn($leadLength);
        $igniter->shouldReceive('getAttribute')->withArgs(['sensor_type'])->once()->andReturn($sensorType);
        $igniter->shouldReceive('getAttribute')->withArgs(['steady_current'])->once()->andReturn($steadyCurrent);
        $igniter->shouldReceive('getAttribute')->withArgs(['temp_rating'])->once()->andReturn($tempRating);
        $igniter->shouldReceive('getAttribute')->withArgs(['time_to_temp'])->once()->andReturn($timeToTemp);
        $igniter->shouldReceive('getAttribute')->withArgs(['amperage'])->once()->andReturn($amperage);
        $igniter->shouldReceive('getAttribute')->withArgs(['cold_resistance'])->once()->andReturn($coldResistance);
        $igniter->shouldReceive('getAttribute')->withArgs(['max_current'])->once()->andReturn($maxCurrent);
        $igniter->shouldReceive('getAttribute')
            ->withArgs(['compression_fitting_diameter'])
            ->once()
            ->andReturn($compressionFittingDiameter);
        $igniter->shouldReceive('getAttribute')->withArgs(['probe_length'])->once()->andReturn($probeLength);
        $igniter->shouldReceive('getAttribute')->withArgs(['rod_angle'])->once()->andReturn($rodAngle);
        $igniter->shouldReceive('getAttribute')->withArgs(['length'])->once()->andReturn($length);
        $igniter->shouldReceive('getAttribute')->withArgs(['height'])->once()->andReturn($height);
        $igniter->shouldReceive('getAttribute')->withArgs(['width'])->once()->andReturn($width);

        $resource = new IgniterResource($igniter);

        $response = $resource->resolve();

        $data = [
            'application'                  => $application,
            'gas_type'                     => $gasType,
            'voltage'                      => $voltage,
            'terminal_type'                => $terminalType,
            'mounting'                     => $mounting,
            'tip_style'                    => $tipStyle,
            'ceramic_block'                => $ceramicBlock,
            'pilot_btu'                    => $pilotBtu,
            'orifice_diameter'             => $orificeDiameter,
            'pilot_tube_length'            => $pilotTubeLength,
            'lead_length'                  => $leadLength,
            'sensor_type'                  => $sensorType,
            'steady_current'               => $steadyCurrent,
            'temp_rating'                  => $tempRating,
            'time_to_temp'                 => $timeToTemp,
            'amperage'                     => $amperage,
            'cold_resistance'              => $coldResistance,
            'max_current'                  => $maxCurrent,
            'compression_fitting_diameter' => $compressionFittingDiameter,
            'probe_length'                 => $probeLength,
            'rod_angle'                    => $rodAngle,
            'length'                       => $length,
            'height'                       => $height,
            'width'                        => $width,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(IgniterResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
