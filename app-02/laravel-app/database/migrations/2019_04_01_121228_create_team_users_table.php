<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTeamUsersTable extends Migration
{
    public function up()
    {
        Schema::create('team_users', function(Blueprint $table) {
            $table->unsignedInteger('team_id');
            $table->unsignedInteger('user_id');
            $table->string('role', 20);

            $table->unique(['team_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::drop('team_users');
    }
}
