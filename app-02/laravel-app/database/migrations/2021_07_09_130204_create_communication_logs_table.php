<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunicationLogsTable extends Migration
{
    const TABLE_NAME = 'communication_logs';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('communication_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('description')->default('success');
            $table->json('request');
            $table->text('response');
            $table->string('source')->nullable();
            $table->json('errors');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
