<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserAdditionalColumns extends Migration
{
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->string('photo')->nullable();
            $table->text('bio')->nullable();
            $table->string('job_title')->nullable();
            $table->string('union')->nullable();
            $table->string('experience_years')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('photo');
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('bio');
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('job_title');
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('union');
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('experience_years');
        });
    }
}
