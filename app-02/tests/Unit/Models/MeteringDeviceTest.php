<?php

namespace Tests\Unit\Models;

use App\Models\IsPart;
use App\Models\MeteringDevice;
use ReflectionException;

class MeteringDeviceTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(MeteringDevice::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(MeteringDevice::tableName(), [
            'id',
            'rated_refrigerant',
            'nominal_capacity',
            'inlet_diameter',
            'inlet_connection_type',
            'outlet_diameter',
            'outlet_connection_type',
            'equalizer',
            'external_equalizer_connection',
            'bidirectional',
            'adjustable',
            'configuration',
            'supply_voltage',
            'motor_type',
            'control_steps',
            'step_rate',
            'orfice_size',
            'capillary_tube_length',
            'number_of_headers',
            'spring_type',
            'check_valve',
            'hermetic',
            'balanced_port',
            'applications',
            'element_size',
            'body_type',
            'thermostatic_charge',
            'mesh_strainer',
            'max_operating_pressures',
            'max_differential_pressure_drop',
            'ambient_temperature',
            'refrigerant_temperature',
            'current',
            'drive_frequency',
            'phase_resistance',
            'compatible_oils',
            'cable_type',
            'max_power_input',
            'step_angle',
            'resolution',
            'connections',
            'closing_steps',
            'minimum_steps',
            'hold_current',
            'percent_duty',
            'stroke',
            'max_internal_leakage',
            'max_external_leakage',
            'drill_size',
        ]);
    }
}
