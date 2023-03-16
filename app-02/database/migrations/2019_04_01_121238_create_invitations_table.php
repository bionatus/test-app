<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInvitationsTable extends Migration
{
    public function up()
    {
        Schema::create('invitations', function(Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedInteger('team_id')->index();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('role')->nullable();
            $table->string('email');
            $table->string('token', 40)->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('invitations');
    }
}
