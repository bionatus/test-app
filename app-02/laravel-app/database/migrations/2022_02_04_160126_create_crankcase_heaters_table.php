<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrankcaseHeatersTable extends Migration
{
    const TABLE_NAME = 'crankcase_heaters';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('watts_power', 10)->nullable();
            $table->string('voltage', 10)->nullable();
            $table->string('shape', 10)->nullable();
            $table->string('min_dimension', 10)->nullable();
            $table->string('max_dimension', 10)->nullable();
            $table->string('probe_length', 10)->nullable();
            $table->string('probe_diameter', 10)->nullable();
            $table->string('lead_length', 10)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
