<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHardStartKitsTable extends Migration
{
    const TABLE_NAME = 'hard_start_kits';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('operating_voltage', 25)->nullable();
            $table->string('max_hp', 10)->nullable();
            $table->string('min_hp', 10)->nullable();
            $table->string('max_tons', 25)->nullable();
            $table->string('min_tons', 25)->nullable();
            $table->string('max_capacitance', 10)->nullable();
            $table->string('min_capacitance', 10)->nullable();
            $table->string('tolerance', 10)->nullable();
            $table->string('torque_increase', 10)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
