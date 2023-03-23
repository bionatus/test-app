<?php

use Database\Seeders\SettingsSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApplicableToColumnToSettingsTable extends Migration
{
    const TABLE_NAME  = 'settings';
    const COLUMN_NAME = 'applicable_to';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string(self::COLUMN_NAME)->nullable()->after('group');
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string(self::COLUMN_NAME)->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn(self::COLUMN_NAME);
        });
    }
}
