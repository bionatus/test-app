<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('notifications', function(Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('created_by')->nullable();
            $table->string('icon', 50)->nullable();
            $table->text('body');
            $table->string('action_text')->nullable();
            $table->text('action_url')->nullable();
            $table->tinyInteger('read')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::drop('notifications');
    }
}
