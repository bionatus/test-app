<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentCallTable extends Migration
{
    const TABLE_NAME         = 'agent_call';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED   = 'completed';
    const STATUS_INVALID     = 'invalid';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('agent_id')
                ->type('integer')
                ->unsigned()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->enum('status', [
                self::STATUS_IN_PROGRESS,
                self::STATUS_COMPLETED,
                self::STATUS_INVALID,
            ])->default(self::STATUS_IN_PROGRESS);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
