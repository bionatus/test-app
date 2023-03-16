<?php

namespace Tests\Unit\Models;

use App\Models\Contactor;
use App\Models\IsPart;
use ReflectionException;

class ContactorTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(Contactor::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Contactor::tableName(), [
            'id',
            'poles',
            'shunts',
            'coil_voltage',
            'operating_voltage',
            'ph',
            'hz',
            'fla',
            'lra',
            'connection_type',
            'termination_type',
            'resistive_amps',
            'noninductive_amps',
            'auxialliary_contacts',
            'push_to_test_window',
            'contactor_type',
            'height',
            'width',
            'length',
            'coil_type',
            'max_hp',
            'fuse_clip_size',
            'enclosure_type',
            'temperature_rating',
            'current_setting_range',
            'reset_type',
            'accessories',
            'overload_relays',
            'overload_time',
            'action',
            'rla',
            'application',
            'switch_current',
            'minimum_wire_size',
            'drop_out_voltage',
            'pole_form',
            'maximum_wire_size',
            'contact_material',
            'inrush_voltage',
            'contact_configuration',
            'pick_up_voltage',
            'max_cold_voltage',
            'series',
            'contactor_design',
            'rated_power',
        ]);
    }
}
