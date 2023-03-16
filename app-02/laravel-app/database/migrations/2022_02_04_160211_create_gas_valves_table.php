<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGasValvesTable extends Migration
{
    const TABLE_NAME = 'gas_valves';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('type_of_gas', 10)->nullable();
            $table->integer('stages')->nullable();
            $table->string('capacity', 200)->nullable();
            $table->string('outlet_orientation', 100)->nullable();
            $table->string('reducer_bushing', 25)->nullable();
            $table->string('inlet_size', 10)->nullable();
            $table->string('outlet_size_type', 36)->nullable();
            $table->string('pilot_outlet_size', 10)->nullable();
            $table->string('factory_settings', 200)->nullable();
            $table->string('max_inlet_pressure', 25)->nullable();
            $table->string('min_adjustable_setting', 25)->nullable();
            $table->string('max_adjustable_setting', 25)->nullable();
            $table->string('terminal_type', 25)->nullable();
            $table->string('electrical_rating', 200)->nullable();
            $table->string('side_outlet_size_type', 10)->nullable();
            $table->string('gas_cock_dial_markings', 50)->nullable();
            $table->string('ambient_temperature', 25)->nullable();
            $table->string('amp_rating', 200)->nullable();
            $table->string('capillary_length', 50)->nullable();
            $table->string('standard_dial', 25)->nullable();
            $table->string('remote_dial', 25)->nullable();
            $table->string('temperature_range', 25)->nullable();
            $table->string('height', 25)->nullable();
            $table->string('length', 25)->nullable();
            $table->string('width', 25)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
