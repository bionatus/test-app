<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\PressureControlResource;
use App\Models\PressureControl;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class PressureControlResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $setpoint            = $this->faker->text(25);
        $reset               = $this->faker->text(25);
        $rangeMinimum        = $this->faker->numberBetween();
        $rangeMaximum        = $this->faker->numberBetween();
        $resetMinimum        = $this->faker->numberBetween();
        $resetMaximum        = $this->faker->numberBetween();
        $differentialMinimum = $this->faker->text(10);
        $differentialMaximum = $this->faker->text(10);
        $operationOfContacts = $this->faker->text(50);
        $switch              = $this->faker->text(25);
        $action              = $this->faker->text(25);
        $resetType           = $this->faker->text(25);
        $connectionType      = $this->faker->text(100);
        $maxAmps             = $this->faker->text(10);
        $maxVolts            = $this->faker->text(10);

        $pressureControl = Mockery::mock(PressureControl::class);
        $pressureControl->shouldReceive('getAttribute')->withArgs(['setpoint'])->once()->andReturn($setpoint);
        $pressureControl->shouldReceive('getAttribute')->withArgs(['reset'])->once()->andReturn($reset);
        $pressureControl->shouldReceive('getAttribute')->withArgs(['range_minimum'])->once()->andReturn($rangeMinimum);
        $pressureControl->shouldReceive('getAttribute')->withArgs(['range_maximum'])->once()->andReturn($rangeMaximum);
        $pressureControl->shouldReceive('getAttribute')->withArgs(['reset_minimum'])->once()->andReturn($resetMinimum);
        $pressureControl->shouldReceive('getAttribute')->withArgs(['reset_maximum'])->once()->andReturn($resetMaximum);
        $pressureControl->shouldReceive('getAttribute')
            ->withArgs(['differential_minimum'])
            ->once()
            ->andReturn($differentialMinimum);
        $pressureControl->shouldReceive('getAttribute')
            ->withArgs(['differential_maximum'])
            ->once()
            ->andReturn($differentialMaximum);
        $pressureControl->shouldReceive('getAttribute')
            ->withArgs(['operation_of_contacts'])
            ->once()
            ->andReturn($operationOfContacts);
        $pressureControl->shouldReceive('getAttribute')->withArgs(['switch'])->once()->andReturn($switch);
        $pressureControl->shouldReceive('getAttribute')->withArgs(['action'])->once()->andReturn($action);
        $pressureControl->shouldReceive('getAttribute')->withArgs(['reset_type'])->once()->andReturn($resetType);
        $pressureControl->shouldReceive('getAttribute')
            ->withArgs(['connection_type'])
            ->once()
            ->andReturn($connectionType);
        $pressureControl->shouldReceive('getAttribute')->withArgs(['max_amps'])->once()->andReturn($maxAmps);
        $pressureControl->shouldReceive('getAttribute')->withArgs(['max_volts'])->once()->andReturn($maxVolts);

        $resource = new PressureControlResource($pressureControl);

        $response = $resource->resolve();

        $data = [
            'setpoint'              => $setpoint,
            'reset'                 => $reset,
            'range_minimum'         => $rangeMinimum,
            'range_maximum'         => $rangeMaximum,
            'reset_minimum'         => $resetMinimum,
            'reset_maximum'         => $resetMaximum,
            'differential_minimum'  => $differentialMinimum,
            'differential_maximum'  => $differentialMaximum,
            'operation_of_contacts' => $operationOfContacts,
            'switch'                => $switch,
            'action'                => $action,
            'reset_type'            => $resetType,
            'connection_type'       => $connectionType,
            'max_amps'              => $maxAmps,
            'max_volts'             => $maxVolts,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(PressureControlResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
