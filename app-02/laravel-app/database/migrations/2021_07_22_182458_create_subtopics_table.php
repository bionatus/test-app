<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubtopicsTable extends Migration
{
    const TABLE_NAME = 'subtopics';

    public function up()
    {
        if ('mysql' === DB::connection()->getName()) {
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('subjects')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
