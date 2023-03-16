<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropYellowIfStandardColumnFromConversionJobsTable extends Migration
{
    public function up()
    {
        Schema::table('conversion_jobs', function(Blueprint $table) {
            $table->dropColumn('yellow_if_standard');
        });
    }

    public function down()
    {
        Schema::table('conversion_jobs', function(Blueprint $table) {
            $table->boolean('yellow_if_standard')->default(false)->after('optional');
        });
    }
}
