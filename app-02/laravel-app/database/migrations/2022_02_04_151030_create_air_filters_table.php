<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAirFiltersTable extends Migration
{
    const TABLE_NAME = 'air_filters';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('media_type', 50)->nullable();
            $table->integer('merv_rating')->nullable();
            $table->string('nominal_width', 10)->nullable();
            $table->string('nominal_length', 10)->nullable();
            $table->string('nominal_depth', 10)->nullable();
            $table->string('actual_width', 10)->nullable();
            $table->string('actual_length', 10)->nullable();
            $table->string('actual_depth', 10)->nullable();
            $table->string('efficiency', 10)->nullable();
            $table->string('max_operating_temp', 10)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
