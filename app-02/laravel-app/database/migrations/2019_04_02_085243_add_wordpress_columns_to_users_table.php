<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWordpressColumnsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->string('user_login')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('legacy_password')->nullable();
            $table->integer('legacy_id')->nullable();
            $table->string('role')->nullable();
            $table->string('company')->nullable();
            $table->string('hvac_supplier')->nullable();
            $table->string('occupation')->nullable();
            $table->string('type_of_services')->nullable();
            $table->string('references')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'legacy_password',
                'legacy_id',
                'role',
                'company',
                'hvac_supplier',
                'occupation',
                'type_of_services',
                'references',
                'address',
                'city',
                'state',
                'zip',
                'country',
            ]);
        });
    }
}
