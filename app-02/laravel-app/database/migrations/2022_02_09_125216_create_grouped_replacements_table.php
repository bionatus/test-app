<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupedReplacementsTable extends Migration
{
    const TABLE_NAME = 'grouped_replacements';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('replacement_id')->constrained('replacements')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('replacement_part_id')->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
