<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunicationsTable extends Migration
{
    const TABLE_NAME = 'communications';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('provider', ['twilio'])->default('twilio');
            $table->string('provider_id');
            $table->enum('channel', ['call', 'chat']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
