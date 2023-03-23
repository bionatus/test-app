<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsersGroupCodeAndAppsColumns extends Migration
{
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->json('apps')->nullable();
            $table->text('group_code')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('apps');
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('group_code');
        });
    }
}
