<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\RelaySwitchTimerSequencerResource;
use App\Models\RelaySwitchTimerSequencer;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class RelaySwitchTimerSequencerResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $poles               = $this->faker->text(25);
        $action              = $this->faker->text(25);
        $coilVoltage         = $this->faker->text(25);
        $ph                  = $this->faker->numberBetween();
        $hz                  = $this->faker->text(100);
        $fla                 = $this->faker->text(100);
        $lra                 = $this->faker->text(100);
        $operatingVoltage    = $this->faker->numberBetween();
        $mountingBase        = $this->faker->text(25);
        $terminalType        = $this->faker->text(50);
        $mountingRelay       = $this->faker->text(25);
        $delayOnMake         = $this->faker->text(100);
        $delayOnBreak        = $this->faker->text(100);
        $adjustable          = $this->faker->text(20);
        $fused               = $this->faker->boolean();
        $throwType           = $this->faker->text(25);
        $mountingType        = $this->faker->text(50);
        $baseType            = $this->faker->text(10);
        $statusIndicator     = $this->faker->text(25);
        $options             = $this->faker->text(100);
        $acContactRating     = $this->faker->text(25);
        $dcContactRating     = $this->faker->text(25);
        $socketCode          = $this->faker->text(10);
        $numberOfPins        = $this->faker->numberBetween();
        $maxSwitchingVoltage = $this->faker->text(25);
        $minSwitchingVoltage = $this->faker->text(25);
        $serviceLife         = $this->faker->text(25);
        $m1M2OnTime          = $this->faker->text(25);
        $m1M2OffTime         = $this->faker->text(25);
        $m3M4OnTime          = $this->faker->text(25);
        $m3M4OffTime         = $this->faker->text(25);
        $m5M6OnTime          = $this->faker->text(25);
        $m5M6OffTime         = $this->faker->text(25);
        $m7M8OnTime          = $this->faker->text(25);
        $m7M8OffTime         = $this->faker->text(25);
        $m9M10OnTime         = $this->faker->text(25);
        $m9M10OffTime        = $this->faker->text(25);
        $resistiveWatts      = $this->faker->text(10);
        $pilotDuty           = $this->faker->text(10);
        $ambientTemperature  = $this->faker->text(50);

        $relaySwitch = Mockery::mock(RelaySwitchTimerSequencer::class);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['poles'])->once()->andReturn($poles);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['action'])->once()->andReturn($action);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['coil_voltage'])->once()->andReturn($coilVoltage);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['ph'])->once()->andReturn($ph);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['hz'])->once()->andReturn($hz);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['fla'])->once()->andReturn($fla);
        $relaySwitch->shouldReceive('getAttribute')
            ->withArgs(['operating_voltage'])
            ->once()
            ->andReturn($operatingVoltage);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['mounting_base'])->once()->andReturn($mountingBase);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['terminal_type'])->once()->andReturn($terminalType);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['mounting_relay'])->once()->andReturn($mountingRelay);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['delay_on_make'])->once()->andReturn($delayOnMake);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['delay_on_break'])->once()->andReturn($delayOnBreak);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['adjustable'])->once()->andReturn($adjustable);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['fused'])->once()->andReturn($fused);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['throw_type'])->once()->andReturn($throwType);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['mounting_type'])->once()->andReturn($mountingType);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['base_type'])->once()->andReturn($baseType);
        $relaySwitch->shouldReceive('getAttribute')
            ->withArgs(['status_indicator'])
            ->once()
            ->andReturn($statusIndicator);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['options'])->once()->andReturn($options);
        $relaySwitch->shouldReceive('getAttribute')
            ->withArgs(['ac_contact_rating'])
            ->once()
            ->andReturn($acContactRating);
        $relaySwitch->shouldReceive('getAttribute')
            ->withArgs(['dc_contact_rating'])
            ->once()
            ->andReturn($dcContactRating);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['socket_code'])->once()->andReturn($socketCode);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['number_of_pins'])->once()->andReturn($numberOfPins);
        $relaySwitch->shouldReceive('getAttribute')
            ->withArgs(['max_switching_voltage'])
            ->once()
            ->andReturn($maxSwitchingVoltage);
        $relaySwitch->shouldReceive('getAttribute')
            ->withArgs(['min_switching_voltage'])
            ->once()
            ->andReturn($minSwitchingVoltage);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['service_life'])->once()->andReturn($serviceLife);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['m1_m2_on_time'])->once()->andReturn($m1M2OnTime);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['m1_m2_off_time'])->once()->andReturn($m1M2OffTime);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['m3_m4_on_time'])->once()->andReturn($m3M4OnTime);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['m3_m4_off_time'])->once()->andReturn($m3M4OffTime);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['m5_m6_on_time'])->once()->andReturn($m5M6OnTime);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['m5_m6_off_time'])->once()->andReturn($m5M6OffTime);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['m7_m8_on_time'])->once()->andReturn($m7M8OnTime);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['m7_m8_off_time'])->once()->andReturn($m7M8OffTime);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['m9_m10_on_time'])->once()->andReturn($m9M10OnTime);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['m9_m10_off_time'])->once()->andReturn($m9M10OffTime);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['resistive_watts'])->once()->andReturn($resistiveWatts);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['lra'])->once()->andReturn($lra);
        $relaySwitch->shouldReceive('getAttribute')->withArgs(['pilot_duty'])->once()->andReturn($pilotDuty);
        $relaySwitch->shouldReceive('getAttribute')
            ->withArgs(['ambient_temperature'])
            ->once()
            ->andReturn($ambientTemperature);

        $resource = new RelaySwitchTimerSequencerResource($relaySwitch);

        $response = $resource->resolve();

        $data = [
            'poles'                 => $poles,
            'action'                => $action,
            'coil_voltage'          => $coilVoltage,
            'ph'                    => $ph,
            'hz'                    => $hz,
            'fla'                   => $fla,
            'operating_voltage'     => $operatingVoltage,
            'mounting_base'         => $mountingBase,
            'terminal_type'         => $terminalType,
            'mounting_relay'        => $mountingRelay,
            'delay_on_make'         => $delayOnMake,
            'delay_on_break'        => $delayOnBreak,
            'adjustable'            => $adjustable,
            'fused'                 => $fused,
            'throw_type'            => $throwType,
            'mounting_type'         => $mountingType,
            'base_type'             => $baseType,
            'status_indicator'      => $statusIndicator,
            'options'               => $options,
            'ac_contact_rating'     => $acContactRating,
            'dc_contact_rating'     => $dcContactRating,
            'socket_code'           => $socketCode,
            'number_of_pins'        => $numberOfPins,
            'max_switching_voltage' => $maxSwitchingVoltage,
            'min_switching_voltage' => $minSwitchingVoltage,
            'service_life'          => $serviceLife,
            'm1_m2_on_time'         => $m1M2OnTime,
            'm1_m2_off_time'        => $m1M2OffTime,
            'm3_m4_on_time'         => $m3M4OnTime,
            'm3_m4_off_time'        => $m3M4OffTime,
            'm5_m6_on_time'         => $m5M6OnTime,
            'm5_m6_off_time'        => $m5M6OffTime,
            'm7_m8_on_time'         => $m7M8OnTime,
            'm7_m8_off_time'        => $m7M8OffTime,
            'm9_m10_on_time'        => $m9M10OnTime,
            'm9_m10_off_time'       => $m9M10OffTime,
            'resistive_watts'       => $resistiveWatts,
            'lra'                   => $lra,
            'pilot_duty'            => $pilotDuty,
            'ambient_temperature'   => $ambientTemperature,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(RelaySwitchTimerSequencerResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
