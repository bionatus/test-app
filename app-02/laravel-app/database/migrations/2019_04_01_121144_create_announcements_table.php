<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAnnouncementsTable extends Migration
{
    public function up()
    {
        Schema::create('announcements', function(Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedInteger('user_id');
            $table->text('body');
            $table->string('action_text')->nullable();
            $table->text('action_url')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('announcements');
    }
}
