<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplacementsTable extends Migration
{
    const TABLE_NAME = 'replacements';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('original_part_id')->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('uuid', 36)->unique();
            $table->string('type');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
