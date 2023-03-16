<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCallLogsTable extends Migration
{
    const TABLE_NAME = 'call_logs';

    public function up()
    {
        Schema::drop(self::TABLE_NAME);
    }

    public function down()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained('calls')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('source')->nullable();
            $table->json('request');
            $table->text('response');
            $table->json('errors');
            $table->timestamps();
        });
    }
}
