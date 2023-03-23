<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePressureControlsTable extends Migration
{
    const TABLE_NAME = 'pressure_controls';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('setpoint', 25)->nullable();
            $table->string('reset', 25)->nullable();
            $table->integer('range_minimum')->nullable();
            $table->integer('range_maximum')->nullable();
            $table->integer('reset_minimum')->nullable();
            $table->integer('reset_maximum')->nullable();
            $table->string('differential_minimum', 10)->nullable();
            $table->string('differential_maximum', 10)->nullable();
            $table->string('operation_of_contacts', 50)->nullable();
            $table->string('switch', 25)->nullable();
            $table->string('action', 25)->nullable();
            $table->string('reset_type', 25)->nullable();
            $table->string('connection_type', 100)->nullable();
            $table->string('max_amps', 10)->nullable();
            $table->string('max_volts', 10)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
