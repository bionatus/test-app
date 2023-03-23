<?php

use Database\Seeders\TopicsSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectToolTable extends Migration
{
    const TABLE_NAME = 'subject_tool';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('tool_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->unique(['subject_id', 'tool_id']);
        });

        Artisan::call('db:seed', ['--class' => TopicsSeeder::class]);
    }

    public function down()
    {
        Schema::dropIfExists('subject_tool');
    }
}
