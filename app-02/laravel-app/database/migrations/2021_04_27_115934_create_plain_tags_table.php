<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlainTagsTable extends Migration
{
    const TABLE_NAME = 'plain_tags';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->char('slug', 250)->unique();
            $table->string('name', 200);
            $table->enum('type', ['general', 'issue', 'more']);
            $table->timestamps();

            $table->unique(['slug', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
