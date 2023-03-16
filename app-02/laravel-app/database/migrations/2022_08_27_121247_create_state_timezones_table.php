<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStateTimezonesTable extends Migration
{
    const TABLE_NAME = 'state_timezones';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->string('country');
            $table->string('state');
            $table->string('timezone');
            $table->timestamps();

            $table->unique(['country', 'state']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
