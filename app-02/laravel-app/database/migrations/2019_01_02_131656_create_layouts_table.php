<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLayoutsTable extends Migration
{
    public function up()
    {
        Schema::create('layouts', function(Blueprint $table) {
            $table->increments('id');
            $table->string('version')->index()->unique();
            $table->json('products');
            $table->json('conversion');
        });
    }

    public function down()
    {
        Schema::dropIfExists('layouts');
    }
}
