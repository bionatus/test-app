<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWheelsTable extends Migration
{
    const TABLE_NAME = 'wheels';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('diameter', 10)->nullable();
            $table->string('width', 10)->nullable();
            $table->string('bore', 25)->nullable();
            $table->string('rotation', 10)->nullable();
            $table->integer('max_rpm')->nullable();
            $table->string('material', 25)->nullable();
            $table->string('keyway', 25)->nullable();
            $table->string('center_disc', 25)->nullable();
            $table->integer('number_hubs')->nullable();
            $table->string('hub_lock', 25)->nullable();
            $table->string('number_setscrews', 10)->nullable();
            $table->integer('number_blades')->nullable();
            $table->string('wheel_type', 25)->nullable();
            $table->string('drive_type', 50)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
