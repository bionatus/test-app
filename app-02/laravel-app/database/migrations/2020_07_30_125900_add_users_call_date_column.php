<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsersCallDateColumn extends Migration
{
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->timestamp('call_date')->nullable();
            $table->unsignedInteger('call_count')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('call_date');
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('call_count')->nullable();
        });
    }
}
