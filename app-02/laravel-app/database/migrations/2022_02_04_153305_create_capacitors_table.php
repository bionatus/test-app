<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCapacitorsTable extends Migration
{
    const TABLE_NAME = 'capacitors';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('microfarads', 50)->nullable();
            $table->string('voltage', 25)->nullable();
            $table->string('shape', 25)->nullable();
            $table->string('tolerance', 10)->nullable();
            $table->string('operating_temperature', 50)->nullable();
            $table->string('depth', 10)->nullable();
            $table->string('height', 10)->nullable();
            $table->string('width', 25)->nullable();
            $table->string('part_number_correction', 100)->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
