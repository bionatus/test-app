<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelaySwitchesTimersSequencersTable extends Migration
{
    const TABLE_NAME = 'relay_switches_timers_sequencers';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('poles', 25)->nullable();
            $table->string('action', 25)->nullable();
            $table->string('coil_voltage', 25)->nullable();
            $table->integer('ph')->nullable();
            $table->string('hz', 100)->nullable();
            $table->string('fla', 100)->nullable();
            $table->string('lra', 100)->nullable();
            $table->integer('operating_voltage')->nullable();
            $table->string('mounting_base', 25)->nullable();
            $table->string('terminal_type', 50)->nullable();
            $table->string('mounting_relay', 25)->nullable();
            $table->string('delay_on_make', 100)->nullable();
            $table->string('delay_on_break', 100)->nullable();
            $table->string('adjustable', 50)->nullable();
            $table->boolean('fused')->nullable();
            $table->string('throw_type', 25)->nullable();
            $table->string('mounting_type', 50)->nullable();
            $table->string('base_type', 10)->nullable();
            $table->string('status_indicator', 25)->nullable();
            $table->string('options', 100)->nullable();
            $table->string('ac_contact_rating', 25)->nullable();
            $table->string('dc_contact_rating', 25)->nullable();
            $table->string('socket_code', 10)->nullable();
            $table->integer('number_of_pins')->nullable();
            $table->string('max_switching_voltage', 25)->nullable();
            $table->string('min_switching_voltage', 25)->nullable();
            $table->string('service_life', 25)->nullable();
            $table->string('m1_m2_on_time', 25)->nullable();
            $table->string('m1_m2_off_time', 25)->nullable();
            $table->string('m3_m4_on_time', 25)->nullable();
            $table->string('m3_m4_off_time', 25)->nullable();
            $table->string('m5_m6_on_time', 25)->nullable();
            $table->string('m5_m6_off_time', 25)->nullable();
            $table->string('m7_m8_on_time', 25)->nullable();
            $table->string('m7_m8_off_time', 25)->nullable();
            $table->string('m9_m10_on_time', 25)->nullable();
            $table->string('m9_m10_off_time', 25)->nullable();
            $table->string('resistive_watts', 10)->nullable();
            $table->string('pilot_duty', 10)->nullable();
            $table->string('ambient_temperature', 50)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}

