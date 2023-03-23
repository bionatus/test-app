<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveSeriesSystemTable extends Migration
{
    const TABLE_NAME = 'series_system';

    public function up()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }

    public function down()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('system_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->unique(['series_id']);
        });
    }
}
