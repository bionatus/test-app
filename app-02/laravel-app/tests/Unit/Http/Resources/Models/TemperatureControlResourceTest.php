<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\TemperatureControlResource;
use App\Models\TemperatureControl;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class TemperatureControlResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $programmable        = $this->faker->text(25);
        $application         = $this->faker->text(30);
        $wifi                = $this->faker->boolean();
        $powerRequirements   = $this->faker->text(30);
        $operatingVoltage    = $this->faker->text(25);
        $switch              = $this->faker->text(25);
        $action              = $this->faker->text(25);
        $operationOfContacts = $this->faker->text(50);
        $adjustable          = $this->faker->boolean();
        $rangeMinimum        = $this->faker->text(25);
        $rangeMaximum        = $this->faker->text(25);
        $resetMinimum        = $this->faker->numberBetween();
        $resetMaximum        = $this->faker->numberBetween();
        $differentialMinimum = $this->faker->text(25);
        $differentialMaximum = $this->faker->text(25);
        $setpoint            = $this->faker->text(10);
        $reset               = $this->faker->text(10);
        $resetType           = $this->faker->text(25);
        $capillaryLength     = $this->faker->randomFloat(2, 0, 100);
        $maxAmps             = $this->faker->text(10);
        $maxVolts            = $this->faker->text(10);
        $replaceableBulb     = $this->faker->boolean();
        $mount               = $this->faker->text(25);

        $temperatureControl = Mockery::mock(TemperatureControl::class);
        $temperatureControl->shouldReceive('getAttribute')
            ->withArgs(['programmable'])
            ->once()
            ->andReturn($programmable);
        $temperatureControl->shouldReceive('getAttribute')->withArgs(['application'])->once()->andReturn($application);
        $temperatureControl->shouldReceive('getAttribute')->withArgs(['wifi'])->once()->andReturn($wifi);
        $temperatureControl->shouldReceive('getAttribute')
            ->withArgs(['power_requirements'])
            ->once()
            ->andReturn($powerRequirements);
        $temperatureControl->shouldReceive('getAttribute')
            ->withArgs(['operating_voltage'])
            ->once()
            ->andReturn($operatingVoltage);
        $temperatureControl->shouldReceive('getAttribute')->withArgs(['switch'])->once()->andReturn($switch);
        $temperatureControl->shouldReceive('getAttribute')->withArgs(['action'])->once()->andReturn($action);
        $temperatureControl->shouldReceive('getAttribute')
            ->withArgs(['operation_of_contacts'])
            ->once()
            ->andReturn($operationOfContacts);
        $temperatureControl->shouldReceive('getAttribute')->withArgs(['adjustable'])->once()->andReturn($adjustable);
        $temperatureControl->shouldReceive('getAttribute')
            ->withArgs(['range_minimum'])
            ->once()
            ->andReturn($rangeMinimum);
        $temperatureControl->shouldReceive('getAttribute')
            ->withArgs(['range_maximum'])
            ->once()
            ->andReturn($rangeMaximum);
        $temperatureControl->shouldReceive('getAttribute')
            ->withArgs(['reset_minimum'])
            ->once()
            ->andReturn($resetMinimum);
        $temperatureControl->shouldReceive('getAttribute')
            ->withArgs(['reset_maximum'])
            ->once()
            ->andReturn($resetMaximum);
        $temperatureControl->shouldReceive('getAttribute')
            ->withArgs(['differential_minimum'])
            ->once()
            ->andReturn($differentialMinimum);
        $temperatureControl->shouldReceive('getAttribute')
            ->withArgs(['differential_maximum'])
            ->once()
            ->andReturn($differentialMaximum);
        $temperatureControl->shouldReceive('getAttribute')->withArgs(['setpoint'])->once()->andReturn($setpoint);
        $temperatureControl->shouldReceive('getAttribute')->withArgs(['reset'])->once()->andReturn($reset);
        $temperatureControl->shouldReceive('getAttribute')->withArgs(['reset_type'])->once()->andReturn($resetType);
        $temperatureControl->shouldReceive('getAttribute')
            ->withArgs(['capillary_length'])
            ->once()
            ->andReturn($capillaryLength);
        $temperatureControl->shouldReceive('getAttribute')->withArgs(['max_amps'])->once()->andReturn($maxAmps);
        $temperatureControl->shouldReceive('getAttribute')->withArgs(['max_volts'])->once()->andReturn($maxVolts);
        $temperatureControl->shouldReceive('getAttribute')
            ->withArgs(['replaceable_bulb'])
            ->once()
            ->andReturn($replaceableBulb);
        $temperatureControl->shouldReceive('getAttribute')->withArgs(['mount'])->once()->andReturn($mount);

        $resource = new TemperatureControlResource($temperatureControl);

        $response = $resource->resolve();

        $data = [
            'programmable'          => $programmable,
            'application'           => $application,
            'wifi'                  => $wifi,
            'power_requirements'    => $powerRequirements,
            'operating_voltage'     => $operatingVoltage,
            'switch'                => $switch,
            'action'                => $action,
            'operation_of_contacts' => $operationOfContacts,
            'adjustable'            => $adjustable,
            'range_minimum'         => $rangeMinimum,
            'range_maximum'         => $rangeMaximum,
            'reset_minimum'         => $resetMinimum,
            'reset_maximum'         => $resetMaximum,
            'differential_minimum'  => $differentialMinimum,
            'differential_maximum'  => $differentialMaximum,
            'setpoint'              => $setpoint,
            'reset'                 => $reset,
            'reset_type'            => $resetType,
            'capillary_length'      => $capillaryLength,
            'max_amps'              => $maxAmps,
            'max_volts'             => $maxVolts,
            'replaceable_bulb'      => $replaceableBulb,
            'mount'                 => $mount,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(TemperatureControlResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
