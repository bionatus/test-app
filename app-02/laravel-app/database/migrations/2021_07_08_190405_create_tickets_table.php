<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    const TABLE_NAME = 'tickets';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->type('integer')
                ->unsigned()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('uuid', 36)->unique();
            $table->string('topic');
            $table->tinyInteger('rating')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
