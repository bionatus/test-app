<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilterDriersAndCoresTable extends Migration
{
    const TABLE_NAME = 'filter_driers_and_cores';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('volume')->nullable();
            $table->string('inlet_diameter', 10)->nullable();
            $table->string('inlet_connection_type', 25)->nullable();
            $table->string('outlet_diameter', 10)->nullable();
            $table->string('outlet_connection_type', 25)->nullable();
            $table->string('direction_of_flow', 25)->nullable();
            $table->string('desiccant_type', 50)->nullable();
            $table->integer('number_of_cores')->nullable();
            $table->string('options', 50)->nullable();
            $table->string('rated_capacity', 25)->nullable();
            $table->text('note')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
