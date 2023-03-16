<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartBrandsTable extends Migration
{
    const TABLE_NAME = 'part_brands';

    public function up()
    {
        Schema::create(self::TABLE_NAME, function(Blueprint $table) {
            $table->id('id');
            $table->string('slug')->unique();
            $table->string('name')->index()->unique();
            $table->string('logo');
            $table->boolean('preferred')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
