<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\GasValveResource;
use App\Models\GasValve;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class GasValveResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $typeOfGas            = $this->faker->text(10);
        $stages               = $this->faker->numberBetween();
        $capacity             = $this->faker->text(200);
        $outletOrientation    = $this->faker->text(100);
        $reducerBushing       = $this->faker->text(25);
        $inletSize            = $this->faker->text(10);
        $outletSizeType       = $this->faker->text(36);
        $pilotOutletSize      = $this->faker->text(10);
        $factorySettings      = $this->faker->text(200);
        $maxInletPressure     = $this->faker->text(25);
        $minAdjustableSetting = $this->faker->text(25);
        $maxAdjustableSetting = $this->faker->text(25);
        $terminalType         = $this->faker->text(25);
        $electricalRating     = $this->faker->text(200);
        $sideOutletSizeType   = $this->faker->text(10);
        $gasCockDialMarkings  = $this->faker->text(50);
        $ambientTemperature   = $this->faker->text(25);
        $ampRating            = $this->faker->text(200);
        $capillaryLength      = $this->faker->text(50);
        $standardDial         = $this->faker->text(25);
        $remoteDial           = $this->faker->text(25);
        $temperatureRange     = $this->faker->text(25);
        $height               = $this->faker->text(25);
        $length               = $this->faker->text(25);
        $width                = $this->faker->text(25);

        $gasValve = Mockery::mock(GasValve::class);
        $gasValve->shouldReceive('getAttribute')->withArgs(['type_of_gas'])->once()->andReturn($typeOfGas);
        $gasValve->shouldReceive('getAttribute')->withArgs(['stages'])->once()->andReturn($stages);
        $gasValve->shouldReceive('getAttribute')->withArgs(['capacity'])->once()->andReturn($capacity);
        $gasValve->shouldReceive('getAttribute')
            ->withArgs(['outlet_orientation'])
            ->once()
            ->andReturn($outletOrientation);
        $gasValve->shouldReceive('getAttribute')->withArgs(['reducer_bushing'])->once()->andReturn($reducerBushing);
        $gasValve->shouldReceive('getAttribute')->withArgs(['inlet_size'])->once()->andReturn($inletSize);
        $gasValve->shouldReceive('getAttribute')->withArgs(['outlet_size_type'])->once()->andReturn($outletSizeType);
        $gasValve->shouldReceive('getAttribute')->withArgs(['pilot_outlet_size'])->once()->andReturn($pilotOutletSize);
        $gasValve->shouldReceive('getAttribute')->withArgs(['factory_settings'])->once()->andReturn($factorySettings);
        $gasValve->shouldReceive('getAttribute')
            ->withArgs(['max_inlet_pressure'])
            ->once()
            ->andReturn($maxInletPressure);
        $gasValve->shouldReceive('getAttribute')
            ->withArgs(['min_adjustable_setting'])
            ->once()
            ->andReturn($minAdjustableSetting);
        $gasValve->shouldReceive('getAttribute')
            ->withArgs(['max_adjustable_setting'])
            ->once()
            ->andReturn($maxAdjustableSetting);
        $gasValve->shouldReceive('getAttribute')->withArgs(['terminal_type'])->once()->andReturn($terminalType);
        $gasValve->shouldReceive('getAttribute')->withArgs(['electrical_rating'])->once()->andReturn($electricalRating);
        $gasValve->shouldReceive('getAttribute')
            ->withArgs(['side_outlet_size_type'])
            ->once()
            ->andReturn($sideOutletSizeType);
        $gasValve->shouldReceive('getAttribute')
            ->withArgs(['gas_cock_dial_markings'])
            ->once()
            ->andReturn($gasCockDialMarkings);
        $gasValve->shouldReceive('getAttribute')
            ->withArgs(['ambient_temperature'])
            ->once()
            ->andReturn($ambientTemperature);
        $gasValve->shouldReceive('getAttribute')->withArgs(['amp_rating'])->once()->andReturn($ampRating);
        $gasValve->shouldReceive('getAttribute')->withArgs(['capillary_length'])->once()->andReturn($capillaryLength);
        $gasValve->shouldReceive('getAttribute')->withArgs(['standard_dial'])->once()->andReturn($standardDial);
        $gasValve->shouldReceive('getAttribute')->withArgs(['remote_dial'])->once()->andReturn($remoteDial);
        $gasValve->shouldReceive('getAttribute')->withArgs(['temperature_range'])->once()->andReturn($temperatureRange);
        $gasValve->shouldReceive('getAttribute')->withArgs(['height'])->once()->andReturn($height);
        $gasValve->shouldReceive('getAttribute')->withArgs(['length'])->once()->andReturn($length);
        $gasValve->shouldReceive('getAttribute')->withArgs(['width'])->once()->andReturn($width);

        $resource = new GasValveResource($gasValve);

        $response = $resource->resolve();

        $data = [
            'type_of_gas'            => $typeOfGas,
            'stages'                 => $stages,
            'capacity'               => $capacity,
            'outlet_orientation'     => $outletOrientation,
            'reducer_bushing'        => $reducerBushing,
            'inlet_size'             => $inletSize,
            'outlet_size_type'       => $outletSizeType,
            'pilot_outlet_size'      => $pilotOutletSize,
            'factory_settings'       => $factorySettings,
            'max_inlet_pressure'     => $maxInletPressure,
            'min_adjustable_setting' => $minAdjustableSetting,
            'max_adjustable_setting' => $maxAdjustableSetting,
            'terminal_type'          => $terminalType,
            'electrical_rating'      => $electricalRating,
            'side_outlet_size_type'  => $sideOutletSizeType,
            'gas_cock_dial_markings' => $gasCockDialMarkings,
            'ambient_temperature'    => $ambientTemperature,
            'amp_rating'             => $ampRating,
            'capillary_length'       => $capillaryLength,
            'standard_dial'          => $standardDial,
            'remote_dial'            => $remoteDial,
            'temperature_range'      => $temperatureRange,
            'height'                 => $height,
            'length'                 => $length,
            'width'                  => $width,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(GasValveResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
