<?php

namespace Tests\Unit\Models;

use App\Models\IsPart;
use App\Models\Motor;
use ReflectionException;

class MotorTest extends ModelTestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_is_part_trait()
    {
        $this->assertUseTrait(Motor::class, IsPart::class);
    }

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Motor::tableName(), [
            'id',
            'motor_type',
            'duty_rating',
            'voltage',
            'ph',
            'hz',
            'run_capacitor_size',
            'start_capacitor_size',
            'rpm',
            'output_hp',
            'frame_type',
            'rotation',
            'speed',
            'shaft_diameter',
            'shaft_keyway',
            'bearing_type',
            'fla',
            'mounting_type',
            'shaft_length',
            'replaceable_bearings',
            'motor_diameter',
            'motor_height',
            'enclosure_type',
            'material_type',
            'weight',
            'protection',
            'rla',
            'lra',
            'service_factor',
            'power_factor',
            'cfm',
            'efficiency',
            'alternate_part_numbers',
            'output_watts',
            'input_watts',
            'ring_size',
            'resilient_ring_dimension',
            'armature_amps',
            'field_amps',
            'service_factor_amps',
            'multi_voltage',
            'rotation_orientation',
            'mounting_angle',
            'shaft_type',
            'conduit_box_positions',
            'torque_type',
            'drive_type',
            'misc',
            'a_dimension',
            'b_dimension',
            'armature_voltage',
            'bissc_rating',
            'wheel_dimensions',
            'braking_torque',
            'capacitor_part_number',
            'constant_torque_speed',
            'cooling_type',
            'correct_part_number',
            'efficiency_type',
            'eisa_rating',
            'electrical_notes',
            'fan_blade_dimensions',
            'field_voltage',
            'frame_diameter',
            'full_load_torque',
            'hub_to_hub',
            'iec_rating',
            'included_with',
            'input_voltage',
            'lead_length',
            'load_factor',
            'mechanical_hp',
            'module_part_number',
            'mount_part_number',
            'nominal_capacity',
            'notes',
            'number_of_poles',
            'operating_temperature',
            'output_voltage',
            'part_type',
            'run_torque',
            'run_type',
            'shaft_dimensions',
            'shaft_orientation',
            'source_info',
            'stack',
            'start_torque',
            'start_type',
            'total_length',
            'trademark_name',
            'variable_torque_speed',
            'winding_type',
            'blower_outlet_type',
        ]);
    }
}
