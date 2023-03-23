<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeConversionColumnNullable extends Migration
{
    public function up()
    {
        Schema::table('layouts', function(Blueprint $table) {
            $table->json('conversion')->nullable()->change();
        });
    }

    public function down()
    {
        //
    }
}
