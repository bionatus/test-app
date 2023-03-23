<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConversionJobsImageColumn extends Migration
{
    public function up()
    {
        Schema::table('conversion_jobs', function(Blueprint $table) {
            $table->string('image')->nullable();
        });
    }

    public function down()
    {
        Schema::table('conversion_jobs', function(Blueprint $table) {
            $table->dropColumn('image');
        });
    }
}
