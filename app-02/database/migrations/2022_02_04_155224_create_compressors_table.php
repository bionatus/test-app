<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompressorsTable extends Migration
{
    const TABLE_NAME = 'compressors';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('rated_refrigerant', 50)->nullable();
            $table->string('oil_type', 25)->nullable();
            $table->string('nominal_capacity_tons', 10)->nullable();
            $table->string('nominal_capacity_btuh', 100)->nullable();
            $table->string('voltage', 25)->nullable();
            $table->string('ph', 25)->nullable();
            $table->string('hz', 25)->nullable();
            $table->string('run_capacitor', 50)->nullable();
            $table->string('start_capacitor', 50)->nullable();
            $table->string('connection_type', 25)->nullable();
            $table->string('suction_inlet_diameter', 25)->nullable();
            $table->string('discharge_diameter', 25)->nullable();
            $table->integer('number_of_cylinders')->nullable();
            $table->integer('number_of_unloaders')->nullable();
            $table->boolean('crankcase_heater')->nullable();
            $table->string('protection', 50)->nullable();
            $table->string('speed', 25)->nullable();
            $table->float('eer', 10, 0)->nullable();
            $table->string('displacement', 10)->nullable();
            $table->string('nominal_hp', 25)->nullable();
            $table->string('nominal_power_watts', 10)->nullable();
            $table->string('fla', 10)->nullable();
            $table->string('lra', 10)->nullable();
            $table->string('rpm', 10)->nullable();
            $table->string('compressor_length', 10)->nullable();
            $table->string('compressor_width', 10)->nullable();
            $table->string('compressor_height', 10)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
