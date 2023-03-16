<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMotorsTable extends Migration
{
    const TABLE_NAME = 'motors';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('motor_type', 100)->nullable();
            $table->string('duty_rating', 50)->nullable();
            $table->string('voltage', 100)->nullable();
            $table->string('ph', 25)->nullable();
            $table->string('hz', 50)->nullable();
            $table->string('run_capacitor_size', 50)->nullable();
            $table->string('start_capacitor_size', 50)->nullable();
            $table->string('rpm', 100)->nullable();
            $table->string('output_hp', 50)->nullable();
            $table->string('frame_type', 50)->nullable();
            $table->string('rotation', 50)->nullable();
            $table->string('speed', 50)->nullable();
            $table->string('shaft_diameter', 25)->nullable();
            $table->string('shaft_keyway', 25)->nullable();
            $table->string('shaft_length', 25)->nullable();
            $table->string('shaft_type', 50)->nullable();
            $table->string('bearing_type', 50)->nullable();
            $table->string('fla', 100)->nullable();
            $table->string('mounting_type', 100)->nullable();
            $table->string('replaceable_bearings', 10)->nullable();
            $table->string('motor_diameter', 10)->nullable();
            $table->string('motor_height', 10)->nullable();
            $table->string('enclosure_type', 25)->nullable();
            $table->string('material_type', 25)->nullable();
            $table->string('weight', 10)->nullable();
            $table->string('protection', 25)->nullable();
            $table->string('rla', 50)->nullable();
            $table->string('lra', 50)->nullable();
            $table->string('service_factor', 50)->nullable();
            $table->string('power_factor', 50)->nullable();
            $table->string('cfm', 50)->nullable();
            $table->string('efficiency', 50)->nullable();
            $table->string('alternate_part_numbers', 100)->nullable();
            $table->string('output_watts', 50)->nullable();
            $table->string('input_watts', 50)->nullable();
            $table->string('ring_size', 50)->nullable();
            $table->string('resilient_ring_dimension', 50)->nullable();
            $table->string('armature_amps', 50)->nullable();
            $table->string('field_amps', 50)->nullable();
            $table->string('service_factor_amps', 50)->nullable();
            $table->string('multi_voltage', 50)->nullable();
            $table->string('rotation_orientation', 50)->nullable();
            $table->string('mounting_angle', 50)->nullable();
            $table->string('conduit_box_positions', 50)->nullable();
            $table->string('torque_type', 50)->nullable();
            $table->string('drive_type', 50)->nullable();
            $table->string('misc', 300)->nullable();
            $table->string('armature_voltage', 50)->nullable();
            $table->string('field_voltage', 50)->nullable();
            $table->string('start_type', 50)->nullable();
            $table->string('output_voltage', 50)->nullable();
            $table->string('trademark_name', 50)->nullable();
            $table->string('module_part_number', 100)->nullable();
            $table->string('included_with', 100)->nullable();
            $table->string('operating_temperature', 50)->nullable();
            $table->string('electrical_notes', 100)->nullable();
            $table->string('constant_torque_speed', 100)->nullable();
            $table->string('variable_torque_speed', 100)->nullable();
            $table->string('start_torque', 100)->nullable();
            $table->string('run_torque', 100)->nullable();
            $table->string('full_load_torque', 100)->nullable();
            $table->string('load_factor', 100)->nullable();
            $table->string('frame_diameter', 100)->nullable();
            $table->string('cooling_type', 100)->nullable();
            $table->string('notes', 100)->nullable();
            $table->string('a_dimension', 100)->nullable();
            $table->string('b_dimension', 100)->nullable();
            $table->string('total_length', 100)->nullable();
            $table->string('efficiency_type', 50)->nullable();
            $table->string('shaft_dimensions', 50)->nullable();
            $table->string('fan_blade_dimensions', 50)->nullable();
            $table->string('stack', 50)->nullable();
            $table->string('part_type', 50)->nullable();
            $table->string('lead_length', 50)->nullable();
            $table->string('capacitor_part_number', 50)->nullable();
            $table->string('eisa_rating', 50)->nullable();
            $table->string('bissc_rating', 50)->nullable();
            $table->string('shaft_orientation', 50)->nullable();
            $table->string('run_type', 50)->nullable();
            $table->string('number_of_poles', 50)->nullable();
            $table->string('correct_part_number', 50)->nullable();
            $table->string('mount_part_number', 50)->nullable();
            $table->string('iec_rating', 50)->nullable();
            $table->string('braking_torque', 50)->nullable();
            $table->string('winding_type', 100)->nullable();
            $table->text('source_info')->nullable();
            $table->string('input_voltage', 25)->nullable();
            $table->string('mechanical_hp', 50)->nullable();
            $table->string('hub_to_hub', 50)->nullable();
            $table->string('nominal_capacity', 50)->nullable();
            $table->string('wheel_dimensions', 50)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
