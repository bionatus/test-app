<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCallsTable extends Migration
{
    const TABLE_NAME                    = 'calls';
    const AGENT_CALL_TABLE_NAME         = 'agent_call';
    const AGENT_CALL_STATUS_RINGING     = 'ringing';
    const AGENT_CALL_STATUS_IN_PROGRESS = 'in_progress';
    const AGENT_CALL_STATUS_COMPLETED   = 'completed';
    const AGENT_CALL_STATUS_DROPPED     = 'dropped';
    const AGENT_CALL_STATUS_INVALID     = 'invalid';
    const STATUS_IN_PROGRESS            = 'in_progress';
    const STATUS_COMPLETED              = 'completed';
    const STATUS_INVALID                = 'invalid';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }

        Schema::drop(self::AGENT_CALL_TABLE_NAME);
        Schema::drop(self::TABLE_NAME);

        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('communications')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('status', [
                self::STATUS_IN_PROGRESS,
                self::STATUS_COMPLETED,
                self::STATUS_INVALID,
            ])->default(self::STATUS_IN_PROGRESS);
            $table->timestamps();
        });

        $this->createAgentCallsTable();
    }

    public function down()
    {
        Schema::drop(self::AGENT_CALL_TABLE_NAME);
        Schema::drop(self::TABLE_NAME);

        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->type('integer')
                ->unsigned()
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->enum('provider', ['twilio']);
            $table->string('provider_id', 34);
            $table->enum('status', [
                self::STATUS_IN_PROGRESS,
                self::STATUS_COMPLETED,
                self::STATUS_INVALID,
            ])->default(self::STATUS_IN_PROGRESS);
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
        });

        $this->createAgentCallsTable();
    }

    private function createAgentCallsTable(): void
    {
        Schema::create(self::AGENT_CALL_TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('agent_id')
                ->type('integer')
                ->unsigned()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->enum('status', [
                self::AGENT_CALL_STATUS_RINGING,
                self::AGENT_CALL_STATUS_IN_PROGRESS,
                self::AGENT_CALL_STATUS_COMPLETED,
                self::AGENT_CALL_STATUS_DROPPED,
                self::AGENT_CALL_STATUS_INVALID,
            ])->default(self::AGENT_CALL_STATUS_RINGING);
            $table->timestamps();
        });
    }
}
