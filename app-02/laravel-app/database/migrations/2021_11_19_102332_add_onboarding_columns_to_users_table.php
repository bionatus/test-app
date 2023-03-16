<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnboardingColumnsToUsersTable extends Migration
{
    const TABLE_NAME = 'users';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->timestamp('verified_at')->nullable();
            $table->integer('manual_download_count')->default(0);
            $table->string('address_2')->after('address')->nullable();
            $table->boolean('hat_requested')->nullable();
            $table->string('public_name')->unique()->after('last_name')->nullable();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('verified_at');
            $table->dropColumn('manual_download_count');
            $table->dropColumn('address_2');
            $table->dropColumn('public_name');
            $table->dropColumn('hat_requested');
        });
    }
}
