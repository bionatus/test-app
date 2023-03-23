<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheavesAndPulleysTable extends Migration
{
    const TABLE_NAME = 'sheaves_and_pulleys';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('belt_type', 10)->nullable();
            $table->integer('number_of_grooves')->nullable();
            $table->string('bore_diameter', 10)->nullable();
            $table->float('outside_diameter', 10, 0)->nullable();
            $table->boolean('adjustable')->nullable();
            $table->string('bore_mate_type', 25)->nullable();
            $table->string('bushing_connection', 25)->nullable();
            $table->string('keyway_types', 25)->nullable();
            $table->string('keyway_height', 25)->nullable();
            $table->string('keyway_width', 25)->nullable();
            $table->float('minimum_dd', 10, 0)->nullable();
            $table->float('maximum_dd', 10, 0)->nullable();
            $table->string('material', 100)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
