<?php

namespace Tests\Unit\Models;

use App\Models\IsPart;
use App\Models\RelaySwitchTimerSequencer;
use ReflectionException;

class RelaySwitchTimerSequencerTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(RelaySwitchTimerSequencer::class, IsPart::class);
    }

    /** @test */
    public function it_hals_expected_columns()
    {
        $this->assertHasExpectedColumns(RelaySwitchTimerSequencer::tableName(), [
            'id',
            'poles',
            'action',
            'coil_voltage',
            'ph',
            'hz',
            'fla',
            'operating_voltage',
            'mounting_base',
            'terminal_type',
            'mounting_relay',
            'delay_on_make',
            'delay_on_break',
            'adjustable',
            'fused',
            'throw_type',
            'mounting_type',
            'base_type',
            'status_indicator',
            'options',
            'ac_contact_rating',
            'dc_contact_rating',
            'socket_code',
            'number_of_pins',
            'max_switching_voltage',
            'min_switching_voltage',
            'service_life',
            'm1_m2_on_time',
            'm1_m2_off_time',
            'm3_m4_on_time',
            'm3_m4_off_time',
            'm5_m6_on_time',
            'm5_m6_off_time',
            'm7_m8_on_time',
            'm7_m8_off_time',
            'm9_m10_on_time',
            'm9_m10_off_time',
            'resistive_watts',
            'lra',
            'pilot_duty',
            'ambient_temperature',
            'rated_voltage',
            'rated_power',
            'resistive_amps',
        ]);
    }
}
