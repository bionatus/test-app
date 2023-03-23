<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConversionJobsRetrofitColumn extends Migration
{
    public function up()
    {
        Schema::table('conversion_jobs', function(Blueprint $table) {
            $table->text('retrofit')->nullable();
        });
    }

    public function down()
    {
        Schema::table('conversion_jobs', function(Blueprint $table) {
            $table->dropColumn('retrofit');
        });
    }
}
