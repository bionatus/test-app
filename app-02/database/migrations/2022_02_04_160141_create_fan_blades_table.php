<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFanBladesTable extends Migration
{
    const TABLE_NAME = 'fan_blades';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('diameter', 25)->nullable();
            $table->integer('number_of_blades')->nullable();
            $table->string('pitch', 25)->nullable();
            $table->string('bore', 25)->nullable();
            $table->string('rotation', 10)->nullable();
            $table->integer('rpm')->nullable();
            $table->string('cfm', 50)->nullable();
            $table->string('bhp', 50)->nullable();
            $table->string('material', 25)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
