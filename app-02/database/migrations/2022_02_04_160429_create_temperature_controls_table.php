<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemperatureControlsTable extends Migration
{
    const TABLE_NAME = 'temperature_controls';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('programmable', 25)->nullable();
            $table->string('application', 30)->nullable();
            $table->boolean('wifi')->nullable();
            $table->string('power_requirements', 30)->nullable();
            $table->string('operating_voltage', 25)->nullable();
            $table->string('switch', 25)->nullable();
            $table->string('action', 25)->nullable();
            $table->string('operation_of_contacts', 50)->nullable();
            $table->boolean('adjustable')->nullable();
            $table->string('range_minimum', 25)->nullable();
            $table->string('range_maximum', 25)->nullable();
            $table->integer('reset_minimum')->nullable();
            $table->integer('reset_maximum')->nullable();
            $table->string('differential_minimum', 25)->nullable();
            $table->string('differential_maximum', 25)->nullable();
            $table->string('setpoint', 10)->nullable();
            $table->string('reset', 10)->nullable();
            $table->string('reset_type', 25)->nullable();
            $table->float('capillary_length', 10, 0)->nullable();
            $table->string('max_amps', 10)->nullable();
            $table->string('max_volts', 10)->nullable();
            $table->boolean('replaceable_bulb')->nullable();
            $table->string('mount', 25)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
