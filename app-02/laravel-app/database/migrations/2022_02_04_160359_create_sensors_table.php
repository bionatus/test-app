<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSensorsTable extends Migration
{
    const TABLE_NAME = 'sensors';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('application', 25)->nullable();
            $table->string('signal_type', 25)->nullable();
            $table->string('measurement_range', 25)->nullable();
            $table->string('connection_type', 25)->nullable();
            $table->string('configuration', 25)->nullable();
            $table->integer('number_of_wires')->nullable();
            $table->string('accuracy', 25)->nullable();
            $table->string('enclosure_rating', 25)->nullable();
            $table->string('lead_length', 25)->nullable();
            $table->string('operating_temperature', 10)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
