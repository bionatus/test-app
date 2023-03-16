<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeltsTable extends Migration
{
    const TABLE_NAME = 'belts';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('family', 25)->nullable();
            $table->string('belt_type', 25)->nullable();
            $table->string('belt_length', 50)->nullable();
            $table->string('pitch', 10)->nullable();
            $table->string('thickness', 10)->nullable();
            $table->string('top_width', 10)->nullable();
            $table->string('temperature_rating', 100)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
