<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserCompleteRegistrationColumn extends Migration
{
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->boolean('registration_completed')->nullable();
            $table->timestamp('registration_completed_at')->nullable();
            $table->text('access_code')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('registration_completed');
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('registration_completed_at');
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('access_code');
        });
    }
}
