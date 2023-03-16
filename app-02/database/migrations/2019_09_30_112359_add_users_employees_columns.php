<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsersEmployeesColumns extends Migration
{
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->text('employees')->nullable();
            $table->text('techs_number')->nullable();
            $table->text('service_manager_name')->nullable();
            $table->text('service_manager_phone')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('employees');
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('techs_number');
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('service_manager_name');
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('service_manager_phone');
        });
    }
}
