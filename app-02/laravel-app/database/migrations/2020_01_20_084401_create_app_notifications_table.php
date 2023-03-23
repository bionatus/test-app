<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('app_notifications', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('schedule_id')->nullable();
            $table->string('push_id')->nullable();
            $table->string('name');
            $table->string('type');
            $table->string('message');
            $table->datetime('date')->nullable();
            $table->string('tag_name')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->boolean('read')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_notifications');
    }
}
