<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsersSalesTrackingColumns extends Migration
{
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->integer('calls_count')->nullable();
            $table->integer('manuals_count')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('calls_count');
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('manuals_count');
        });
    }
}
