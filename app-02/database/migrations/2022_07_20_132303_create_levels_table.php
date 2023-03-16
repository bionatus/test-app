<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLevelsTable extends Migration
{
    const TABLE_NAME = 'levels';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('slug')->unique();
            $table->unsignedInteger('from');
            $table->unsignedInteger('to')->nullable();
            $table->unsignedDecimal('coefficient', 4);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
