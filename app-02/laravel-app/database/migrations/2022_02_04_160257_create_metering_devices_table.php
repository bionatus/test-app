<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeteringDevicesTable extends Migration
{
    const TABLE_NAME = 'metering_devices';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('rated_refrigerant', 100)->nullable();
            $table->string('nominal_capacity', 100)->nullable();
            $table->string('inlet_diameter', 10)->nullable();
            $table->string('inlet_connection_type', 10)->nullable();
            $table->string('outlet_diameter', 10)->nullable();
            $table->string('outlet_connection_type', 10)->nullable();
            $table->string('equalizer', 25)->nullable();
            $table->string('external_equalizer_connection', 25)->nullable();
            $table->boolean('bidirectional')->nullable();
            $table->boolean('adjustable')->nullable();
            $table->string('configuration', 25)->nullable();
            $table->string('supply_voltage', 25)->nullable();
            $table->string('motor_type', 100)->nullable();
            $table->integer('control_steps')->nullable();
            $table->string('step_rate', 100)->nullable();
            $table->float('orfice_size', 10, 0)->nullable();
            $table->string('capillary_tube_length', 10)->nullable();
            $table->integer('number_of_headers')->nullable();
            $table->string('spring_type', 10)->nullable();
            $table->string('check_valve', 25)->nullable();
            $table->boolean('hermetic')->nullable();
            $table->boolean('balanced_port')->nullable();
            $table->string('applications', 100)->nullable();
            $table->string('element_size', 25)->nullable();
            $table->string('body_type', 25)->nullable();
            $table->string('thermostatic_charge', 25)->nullable();
            $table->boolean('mesh_strainer')->nullable();
            $table->string('max_operating_pressures', 25)->nullable();
            $table->string('max_differential_pressure_drop', 25)->nullable();
            $table->string('ambient_temperature', 50)->nullable();
            $table->string('refrigerant_temperature', 25)->nullable();
            $table->string('current', 25)->nullable();
            $table->string('drive_frequency', 10)->nullable();
            $table->string('phase_resistance', 25)->nullable();
            $table->string('compatible_oils', 100)->nullable();
            $table->string('cable_type', 50)->nullable();
            $table->string('max_power_input', 25)->nullable();
            $table->string('step_angle', 25)->nullable();
            $table->string('resolution', 25)->nullable();
            $table->string('connections', 25)->nullable();
            $table->string('closing_steps', 25)->nullable();
            $table->integer('minimum_steps')->nullable();
            $table->string('hold_current', 25)->nullable();
            $table->string('percent_duty', 10)->nullable();
            $table->string('stroke', 25)->nullable();
            $table->string('max_internal_leakage', 25)->nullable();
            $table->string('max_external_leakage', 25)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
