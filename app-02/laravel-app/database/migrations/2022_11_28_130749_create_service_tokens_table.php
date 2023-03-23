<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceTokensTable extends Migration
{
    const TABLE_NAME = 'service_tokens';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->string('service_name');
            $table->string('token_name');
            $table->text('value');
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->unique(['service_name', 'token_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
