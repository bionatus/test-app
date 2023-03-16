<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpecificationsColumnsToPartsSpecificTables extends Migration
{
    const PARTS_TABLE_NAME                            = 'parts';
    const COMPRESSORS_TABLE_NAME                      = 'compressors';
    const CONTACTORS_TABLE_NAME                       = 'contactors';
    const GAS_VALVES_TABLE_NAME                       = 'gas_valves';
    const HARD_START_KITS_TABLE_NAME                  = 'hard_start_kits';
    const METERING_DEVICES_TABLE_NAME                 = 'metering_devices';
    const MOTORS_TABLE_NAME                           = 'motors';
    const OTHERS_TABLE_NAME                           = 'others';
    const PRESSURE_CONTROLS_TABLE_NAME                = 'pressure_controls';
    const RELAY_SWITCHES_TIMERS_SEQUENCERS_TABLE_NAME = 'relay_switches_timers_sequencers';

    public function up()
    {
        Schema::table(self::PARTS_TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('created_at');
        });
        Schema::table(self::PARTS_TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('updated_at');
        });
        Schema::table(self::PARTS_TABLE_NAME, function(Blueprint $table) {
            $table->string('subcategory', 255)->nullable();
        });

        Schema::table(self::COMPRESSORS_TABLE_NAME, function(Blueprint $table) {
            $table->string('run_type', 50)->nullable();
            $table->string('oil_factory_charge', 50)->nullable();
            $table->string('run_capacitor_part_number', 50)->nullable();
            $table->string('rated_conditions', 200)->nullable();
            $table->string('efficiency_type', 50)->nullable();
            $table->string('start_capacitor_part_number', 50)->nullable();
            $table->string('heating_type', 50)->nullable();
            $table->string('discharge_height', 50)->nullable();
            $table->string('suction_height', 50)->nullable();
            $table->string('capacitor_type', 50)->nullable();
            $table->string('unloader_type', 50)->nullable();
            $table->string('start_type', 100)->nullable();
            $table->string('process_connection_diameter', 50)->nullable();
            $table->string('oil_recharge', 50)->nullable();
            $table->string('capacity_watts', 50)->nullable();
            $table->string('capacity_mbh', 200)->nullable();
        });

        Schema::table(self::CONTACTORS_TABLE_NAME, function(Blueprint $table) {
            $table->string('minimum_wire_size', 50)->nullable();
            $table->string('drop_out_voltage', 50)->nullable();
            $table->string('pole_form', 50)->nullable();
            $table->string('maximum_wire_size', 50)->nullable();
            $table->string('contact_material', 50)->nullable();
            $table->string('inrush_voltage', 100)->nullable();
            $table->string('contact_configuration', 50)->nullable();
            $table->string('pick_up_voltage', 50)->nullable();
            $table->string('max_cold_voltage', 50)->nullable();
            $table->string('series', 50)->nullable();
            $table->string('contactor_design', 50)->nullable();
            $table->string('rated_power', 50)->nullable();
        });

        Schema::table(self::GAS_VALVES_TABLE_NAME, function(Blueprint $table) {
            $table->string('opening_speed', 50)->nullable();
        });

        Schema::table(self::HARD_START_KITS_TABLE_NAME, function(Blueprint $table) {
            $table->string('capacitor_size', 25)->nullable();
            $table->string('capacitor_voltage', 25)->nullable();
            $table->string('hard_start_type', 50)->nullable();
        });

        Schema::table(self::METERING_DEVICES_TABLE_NAME, function(Blueprint $table) {
            $table->string('drill_size', 50)->nullable();
        });

        Schema::table(self::MOTORS_TABLE_NAME, function(Blueprint $table) {
            $table->string('blower_outlet_type', 50)->nullable();
        });

        Schema::table(self::OTHERS_TABLE_NAME, function(Blueprint $table) {
            $table->string('description', 100)->nullable();
        });

        Schema::table(self::PRESSURE_CONTROLS_TABLE_NAME, function(Blueprint $table) {
            $table->string('pole_form', 100)->nullable();
            $table->string('fla', 50)->nullable();
            $table->string('rated_hz', 50)->nullable();
            $table->string('rated_voltage', 50)->nullable();
            $table->string('lra', 50)->nullable();
            $table->string('temperature_rating', 50)->nullable();
            $table->string('tolerance', 100)->nullable();
        });

        Schema::table(self::RELAY_SWITCHES_TIMERS_SEQUENCERS_TABLE_NAME, function(Blueprint $table) {
            $table->string('rated_voltage', 50)->nullable();
            $table->string('rated_power', 100)->nullable();
            $table->string('resistive_amps', 100)->nullable();
        });
    }

    public function down()
    {
        Schema::table(self::PARTS_TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('subcategory');
            $table->timestamps();
        });

        Schema::table(self::COMPRESSORS_TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('run_type');
            $table->dropColumn('oil_factory_charge');
            $table->dropColumn('rated_conditions');
            $table->dropColumn('efficiency_type');
            $table->dropColumn('start_capacitor_part_number');
            $table->dropColumn('heating_type');
            $table->dropColumn('discharge_height');
            $table->dropColumn('suction_height');
            $table->dropColumn('capacitor_type');
            $table->dropColumn('unloader_type');
            $table->dropColumn('start_type');
            $table->dropColumn('process_connection_diameter');
            $table->dropColumn('oil_recharge');
            $table->dropColumn('capacity_watts');
            $table->dropColumn('capacity_mbh');
        });

        Schema::table(self::CONTACTORS_TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('minimum_wire_size');
            $table->dropColumn('drop_out_voltage');
            $table->dropColumn('pole_form');
            $table->dropColumn('maximum_wire_size');
            $table->dropColumn('contact_material');
            $table->dropColumn('inrush_voltage');
            $table->dropColumn('contact_configuration');
            $table->dropColumn('pick_up_voltage');
            $table->dropColumn('max_cold_voltage');
            $table->dropColumn('series');
            $table->dropColumn('contactor_design');
            $table->dropColumn('rated_power');
        });

        Schema::table(self::GAS_VALVES_TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('opening_speed');
        });

        Schema::table(self::HARD_START_KITS_TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('capacitor_size');
            $table->dropColumn('capacitor_voltage');
            $table->dropColumn('hard_start_type');
        });

        Schema::table(self::METERING_DEVICES_TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('drill_size');
        });

        Schema::table(self::MOTORS_TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('blower_outlet_type');
        });

        Schema::table(self::OTHERS_TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table(self::PRESSURE_CONTROLS_TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('pole_form');
            $table->dropColumn('fla');
            $table->dropColumn('rated_hz');
            $table->dropColumn('rated_voltage');
            $table->dropColumn('lra');
            $table->dropColumn('temperature_rating');
            $table->dropColumn('tolerance');
        });

        Schema::table(self::RELAY_SWITCHES_TIMERS_SEQUENCERS_TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('rated_voltage');
            $table->dropColumn('rated_power');
            $table->dropColumn('resistive_amps');
        });
    }
}
