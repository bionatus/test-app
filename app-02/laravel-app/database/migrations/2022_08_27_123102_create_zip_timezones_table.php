<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZipTimezonesTable extends Migration
{
    const TABLE_NAME = 'zip_timezones';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->string('country', 50);
            $table->string('state', 50);
            $table->string('county', 50);
            $table->string('city', 50);
            $table->string('zip');
            $table->string('timezone');
            $table->timestamps();

            $table->unique(['country', 'state', 'county', 'city']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
