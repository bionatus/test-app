<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\MeteringDeviceResource;
use App\Models\MeteringDevice;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class MeteringDeviceResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $ratedRefrigerant            = $this->faker->text(100);
        $nominalCapacity             = $this->faker->text(100);
        $inletDiameter               = $this->faker->text(10);
        $inletConnectionType         = $this->faker->text(10);
        $outletDiameter              = $this->faker->text(10);
        $outletConnectionType        = $this->faker->text(10);
        $equalizer                   = $this->faker->text(25);
        $externalEqualizerConnection = $this->faker->text(25);
        $bidirectional               = $this->faker->boolean();
        $adjustable                  = $this->faker->boolean();
        $configuration               = $this->faker->text(25);
        $supplyVoltage               = $this->faker->text(25);
        $motorType                   = $this->faker->text(100);
        $controlSteps                = $this->faker->randomNumber();
        $stepRate                    = $this->faker->text(100);
        $orficeSize                  = $this->faker->randomFloat(2, 0, 100);
        $capillaryTubeLength         = $this->faker->text(10);
        $numberOfHeaders             = $this->faker->randomNumber();
        $springType                  = $this->faker->text(10);
        $checkValve                  = $this->faker->text(25);
        $hermetic                    = $this->faker->boolean();
        $balancedPort                = $this->faker->boolean();
        $applications                = $this->faker->text(100);
        $elementSize                 = $this->faker->text(25);
        $bodyType                    = $this->faker->text(25);
        $thermostaticCharge          = $this->faker->text(25);
        $meshStrainer                = $this->faker->boolean();
        $maxOperatingPressures       = $this->faker->text(25);
        $maxDifferentialPressureDrop = $this->faker->text(25);
        $ambientTemperature          = $this->faker->text(50);
        $refrigerantTemperature      = $this->faker->text(25);
        $current                     = $this->faker->text(25);
        $driveFrequency              = $this->faker->text(10);
        $phaseResistance             = $this->faker->text(25);
        $compatibleOils              = $this->faker->text(100);
        $cableType                   = $this->faker->text(50);
        $maxPowerInput               = $this->faker->text(25);
        $stepAngle                   = $this->faker->text(25);
        $resolution                  = $this->faker->text(25);
        $connections                 = $this->faker->text(25);
        $closingSteps                = $this->faker->text(25);
        $minimumSteps                = $this->faker->numberBetween();
        $holdCurrent                 = $this->faker->text(25);
        $percentDuty                 = $this->faker->text(10);
        $stroke                      = $this->faker->text(25);
        $maxInternalLeakage          = $this->faker->text(25);
        $maxExternalLeakage          = $this->faker->text(25);

        $meteringDevice = Mockery::mock(MeteringDevice::class);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['rated_refrigerant'])
            ->once()
            ->andReturn($ratedRefrigerant);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['nominal_capacity'])
            ->once()
            ->andReturn($nominalCapacity);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['inlet_diameter'])->once()->andReturn($inletDiameter);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['inlet_connection_type'])
            ->once()
            ->andReturn($inletConnectionType);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['outlet_diameter'])
            ->once()
            ->andReturn($outletDiameter);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['outlet_connection_type'])
            ->once()
            ->andReturn($outletConnectionType);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['equalizer'])->once()->andReturn($equalizer);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['external_equalizer_connection'])
            ->once()
            ->andReturn($externalEqualizerConnection);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['bidirectional'])->once()->andReturn($bidirectional);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['adjustable'])->once()->andReturn($adjustable);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['configuration'])->once()->andReturn($configuration);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['supply_voltage'])->once()->andReturn($supplyVoltage);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['motor_type'])->once()->andReturn($motorType);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['control_steps'])->once()->andReturn($controlSteps);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['step_rate'])->once()->andReturn($stepRate);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['orfice_size'])->once()->andReturn($orficeSize);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['capillary_tube_length'])
            ->once()
            ->andReturn($capillaryTubeLength);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['number_of_headers'])
            ->once()
            ->andReturn($numberOfHeaders);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['spring_type'])->once()->andReturn($springType);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['check_valve'])->once()->andReturn($checkValve);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['hermetic'])->once()->andReturn($hermetic);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['balanced_port'])->once()->andReturn($balancedPort);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['applications'])->once()->andReturn($applications);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['element_size'])->once()->andReturn($elementSize);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['body_type'])->once()->andReturn($bodyType);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['thermostatic_charge'])
            ->once()
            ->andReturn($thermostaticCharge);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['mesh_strainer'])->once()->andReturn($meshStrainer);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['max_operating_pressures'])
            ->once()
            ->andReturn($maxOperatingPressures);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['max_differential_pressure_drop'])
            ->once()
            ->andReturn($maxDifferentialPressureDrop);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['ambient_temperature'])
            ->once()
            ->andReturn($ambientTemperature);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['refrigerant_temperature'])
            ->once()
            ->andReturn($refrigerantTemperature);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['current'])->once()->andReturn($current);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['drive_frequency'])
            ->once()
            ->andReturn($driveFrequency);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['phase_resistance'])
            ->once()
            ->andReturn($phaseResistance);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['compatible_oils'])
            ->once()
            ->andReturn($compatibleOils);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['cable_type'])->once()->andReturn($cableType);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['max_power_input'])
            ->once()
            ->andReturn($maxPowerInput);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['step_angle'])->once()->andReturn($stepAngle);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['resolution'])->once()->andReturn($resolution);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['connections'])->once()->andReturn($connections);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['closing_steps'])->once()->andReturn($closingSteps);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['minimum_steps'])->once()->andReturn($minimumSteps);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['hold_current'])->once()->andReturn($holdCurrent);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['percent_duty'])->once()->andReturn($percentDuty);
        $meteringDevice->shouldReceive('getAttribute')->withArgs(['stroke'])->once()->andReturn($stroke);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['max_internal_leakage'])
            ->once()
            ->andReturn($maxInternalLeakage);
        $meteringDevice->shouldReceive('getAttribute')
            ->withArgs(['max_external_leakage'])
            ->once()
            ->andReturn($maxExternalLeakage);

        $resource = new MeteringDeviceResource($meteringDevice);

        $response = $resource->resolve();

        $data = [
            'rated_refrigerant'              => $ratedRefrigerant,
            'nominal_capacity'               => $nominalCapacity,
            'inlet_diameter'                 => $inletDiameter,
            'inlet_connection_type'          => $inletConnectionType,
            'outlet_diameter'                => $outletDiameter,
            'outlet_connection_type'         => $outletConnectionType,
            'equalizer'                      => $equalizer,
            'external_equalizer_connection'  => $externalEqualizerConnection,
            'bidirectional'                  => $bidirectional,
            'adjustable'                     => $adjustable,
            'configuration'                  => $configuration,
            'supply_voltage'                 => $supplyVoltage,
            'motor_type'                     => $motorType,
            'control_steps'                  => $controlSteps,
            'step_rate'                      => $stepRate,
            'orfice_size'                    => $orficeSize,
            'capillary_tube_length'          => $capillaryTubeLength,
            'number_of_headers'              => $numberOfHeaders,
            'spring_type'                    => $springType,
            'check_valve'                    => $checkValve,
            'hermetic'                       => $hermetic,
            'balanced_port'                  => $balancedPort,
            'applications'                   => $applications,
            'element_size'                   => $elementSize,
            'body_type'                      => $bodyType,
            'thermostatic_charge'            => $thermostaticCharge,
            'mesh_strainer'                  => $meshStrainer,
            'max_operating_pressures'        => $maxOperatingPressures,
            'max_differential_pressure_drop' => $maxDifferentialPressureDrop,
            'ambient_temperature'            => $ambientTemperature,
            'refrigerant_temperature'        => $refrigerantTemperature,
            'current'                        => $current,
            'drive_frequency'                => $driveFrequency,
            'phase_resistance'               => $phaseResistance,
            'compatible_oils'                => $compatibleOils,
            'cable_type'                     => $cableType,
            'max_power_input'                => $maxPowerInput,
            'step_angle'                     => $stepAngle,
            'resolution'                     => $resolution,
            'connections'                    => $connections,
            'closing_steps'                  => $closingSteps,
            'minimum_steps'                  => $minimumSteps,
            'hold_current'                   => $holdCurrent,
            'percent_duty'                   => $percentDuty,
            'stroke'                         => $stroke,
            'max_internal_leakage'           => $maxInternalLeakage,
            'max_external_leakage'           => $maxExternalLeakage,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(MeteringDeviceResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
