<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    const TABLE_NAME = 'settings';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('slug', 250)->unique();
            $table->enum('group', ['notification', 'agent']);
            $table->enum('type', ['boolean', 'string', 'integer', 'double'])->default('string');
            $table->string('value');
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
