<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    const TABLE_NAME = 'comments';

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
            $table->foreignId('post_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('uuid', 36)->unique();
            $table->string('message', 2000);
            $table->boolean('solution')->nullable();
            $table->timestamps();
            $table->unique(['post_id', 'solution']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
