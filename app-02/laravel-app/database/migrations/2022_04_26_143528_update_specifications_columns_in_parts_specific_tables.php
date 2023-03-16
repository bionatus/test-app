<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSpecificationsColumnsInPartsSpecificTables extends Migration
{
    const IGNITERS_TABLE_NAME                         = 'igniters';
    const METERING_DEVICES_TABLE_NAME                 = 'metering_devices';
    const SENSORS_TABLE_NAME                          = 'sensors';
    const WHEELS_TABLE_NAME                           = 'wheels';
    const AIR_FILTERS_TABLE_NAME                      = 'air_filters';
    const BELTS_TABLE_NAME                            = 'belts';
    const FILTER_DRIERS_AND_CORES_TABLE_NAME          = 'filter_driers_and_cores';
    const CAPACITORS_TABLE_NAME                       = 'capacitors';
    const CRANKCASE_HEATERS_TABLE_NAME                = 'crankcase_heaters';
    const GAS_VALVES_TABLE_NAME                       = 'gas_valves';
    const MOTORS_TABLE_NAME                           = 'motors';
    const FAN_BLADES_TABLE_NAME                       = 'fan_blades';
    const PRESSURE_CONTROLS_TABLE_NAME                = 'pressure_controls';
    const RELAY_SWITCHES_TIMERS_SEQUENCERS_TABLE_NAME = 'relay_switches_timers_sequencers';
    const SHEAVES_AND_PULLEYS_TABLE_NAME              = 'sheaves_and_pulleys';
    const TEMPERATURE_CONTROLS                        = 'temperature_controls';

    public function up()
    {
        Schema::table(self::IGNITERS_TABLE_NAME, function(Blueprint $table) {
            $table->string('lead_length', 25)->nullable()->change();
            $table->string('gas_type', 25)->nullable()->change();
            $table->string('terminal_type', 25)->nullable()->change();
        });

        Schema::table(self::METERING_DEVICES_TABLE_NAME, function(Blueprint $table) {
            $table->string('inlet_diameter', 25)->nullable()->change();
            $table->string('outlet_diameter', 25)->nullable()->change();
            $table->string('capillary_tube_length', 25)->nullable()->change();
            $table->string('inlet_connection_type', 25)->nullable()->change();
            $table->string('outlet_connection_type', 25)->nullable()->change();
            $table->string('orfice_size', 50)->nullable()->change();
        });

        Schema::table(self::SENSORS_TABLE_NAME, function(Blueprint $table) {
            $table->string('connection_type', 50)->nullable()->change();
            $table->string('signal_type', 50)->nullable()->change();
            $table->string('measurement_range', 100)->nullable()->change();
            $table->string('accuracy', 100)->nullable()->change();
            $table->string('operating_temperature', 50)->nullable()->change();
        });

        Schema::table(self::WHEELS_TABLE_NAME, function(Blueprint $table) {
            $table->string('diameter', 25)->nullable()->change();
            $table->string('width', 25)->nullable()->change();
        });

        Schema::table(self::AIR_FILTERS_TABLE_NAME, function(Blueprint $table) {
            $table->string('nominal_width', 25)->nullable()->change();
            $table->string('nominal_length', 25)->nullable()->change();
            $table->string('nominal_depth', 25)->nullable()->change();
            $table->string('actual_width', 25)->nullable()->change();
            $table->string('actual_length', 25)->nullable()->change();
            $table->string('actual_depth', 25)->nullable()->change();
        });

        Schema::table(self::BELTS_TABLE_NAME, function(Blueprint $table) {
            $table->string('thickness', 25)->nullable()->change();
            $table->string('top_width', 25)->nullable()->change();
        });

        Schema::table(self::FILTER_DRIERS_AND_CORES_TABLE_NAME, function(Blueprint $table) {
            $table->string('inlet_diameter', 25)->nullable()->change();
            $table->string('outlet_diameter', 25)->nullable()->change();
        });

        Schema::table(self::CAPACITORS_TABLE_NAME, function(Blueprint $table) {
            $table->string('height', 25)->nullable()->change();
            $table->string('depth', 25)->nullable()->change();
        });

        Schema::table(self::CRANKCASE_HEATERS_TABLE_NAME, function(Blueprint $table) {
            $table->string('voltage', 25)->nullable()->change();
            $table->string('min_dimension', 25)->nullable()->change();
            $table->string('max_dimension', 25)->nullable()->change();
            $table->string('probe_length', 25)->nullable()->change();
            $table->string('probe_diameter', 25)->nullable()->change();
            $table->string('lead_length', 25)->nullable()->change();
        });

        Schema::table(self::GAS_VALVES_TABLE_NAME, function(Blueprint $table) {
            $table->string('inlet_size', 25)->nullable()->change();
            $table->string('pilot_outlet_size', 25)->nullable()->change();
            $table->string('side_outlet_size_type', 25)->nullable()->change();
            $table->string('type_of_gas', 50)->nullable()->change();
            $table->string('stages', 50)->nullable()->change();
        });

        Schema::table(self::MOTORS_TABLE_NAME, function(Blueprint $table) {
            $table->string('motor_diameter', 25)->nullable()->change();
        });

        Schema::table(self::FAN_BLADES_TABLE_NAME, function(Blueprint $table) {
            $table->string('material', 50)->nullable()->change();
        });

        Schema::table(self::PRESSURE_CONTROLS_TABLE_NAME, function(Blueprint $table) {
            $table->string('setpoint', 100)->nullable()->change();
            $table->string('range_minimum', 50)->nullable()->change();
            $table->string('range_maximum', 50)->nullable()->change();
            $table->string('action', 50)->nullable()->change();
        });

        Schema::table(self::RELAY_SWITCHES_TIMERS_SEQUENCERS_TABLE_NAME, function(Blueprint $table) {
            $table->string('poles', 100)->nullable()->change();
            $table->string('action', 100)->nullable()->change();
            $table->string('ph', 50)->nullable()->change();
            $table->string('operating_voltage', 100)->nullable()->change();
        });

        Schema::table(self::SHEAVES_AND_PULLEYS_TABLE_NAME, function(Blueprint $table) {
            $table->string('belt_type', 50)->nullable()->change();
            $table->string('bore_diameter', 50)->nullable()->change();
            $table->string('outside_diameter', 50)->nullable()->change();
        });

        Schema::table(self::TEMPERATURE_CONTROLS, function(Blueprint $table) {
            $table->string('application', 50)->nullable()->change();
            $table->string('reset_minimum', 50)->nullable()->change();
            $table->string('reset_maximum', 50)->nullable()->change();
            $table->string('setpoint', 50)->nullable()->change();
        });
    }

    public function down()
    {
        // Ignoring down as it could cause truncate errors
    }
}
