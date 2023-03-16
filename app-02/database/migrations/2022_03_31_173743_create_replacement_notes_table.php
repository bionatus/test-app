<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReplacementNotesTable extends Migration
{
    const TABLE_NAME = 'replacement_notes';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('replacement_id')->constrained('replacements')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('value');
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
