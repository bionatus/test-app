<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateApiTokensTable extends Migration
{
    public function up()
    {
        Schema::create('api_tokens', function(Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedInteger('user_id');
            $table->string('name');
            $table->string('token', 100)->unique();
            $table->text('metadata');
            $table->tinyInteger('transient')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'expires_at']);
        });
    }

    public function down()
    {
        Schema::drop('api_tokens');
    }
}
