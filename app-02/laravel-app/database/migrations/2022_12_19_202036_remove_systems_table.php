<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveSystemsTable extends Migration
{
    const TABLE_NAME = 'systems';

    public function up()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }

    public function down()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->char('slug', 250)->unique();
            $table->string('name');
            $table->timestamps();
        });
    }
}
