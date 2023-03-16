<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsersHubspotIdColumn extends Migration
{
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->unsignedInteger('hubspot_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('hubspot_id');
        });
    }
}
