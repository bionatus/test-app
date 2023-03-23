<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartNotesTable extends Migration
{
    const TABLE_NAME = 'part_notes';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->unique()->constrained('parts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('value');
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
