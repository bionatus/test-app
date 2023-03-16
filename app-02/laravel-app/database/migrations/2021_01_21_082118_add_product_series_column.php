<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductSeriesColumn extends Migration
{
    public function up()
    {
        Schema::table('products', function(Blueprint $table) {
            $table->bigInteger('series_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('products', function(Blueprint $table) {
            $table->dropColumn('series_id');
        });
    }
}
