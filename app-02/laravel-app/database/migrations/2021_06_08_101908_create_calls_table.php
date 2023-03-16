<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallsTable extends Migration
{
    const TABLE_NAME = 'calls';

    public function up()
    {
        if ('sqlite' === DB::connection()->getName()) {
            $this->upSqlite();

            return;
        }

        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->type('integer')
                ->unsigned()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->enum('provider', ['twilio']);
            $table->string('provider_id', 34);
            $table->enum('status', ['in_progress', 'completed', 'invalid'])->default('in_progress');
            $table->json('payloads');
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }

    private function upSqlite(): void
    {
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
            $table->enum('status', ['in_progress', 'completed', 'invalid'])->default('in_progress');
            $table->json('payloads');
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
        });
    }
}
