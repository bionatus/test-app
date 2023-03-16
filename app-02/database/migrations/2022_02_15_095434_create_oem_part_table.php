<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOemPartTable extends Migration
{
    const TABLE_NAME = 'oem_part';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id();
            $table->foreignId('oem_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('part_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('uid', 36)->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
