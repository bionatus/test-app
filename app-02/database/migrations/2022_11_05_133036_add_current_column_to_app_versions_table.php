<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrentColumnToAppVersionsTable extends Migration
{
    const TABLE_NAME = 'app_versions';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('current')->nullable()->after('version');
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->renameColumn('version', 'min');
        });

        DB::statement('UPDATE ' . self::TABLE_NAME . ' SET current  = "7.0.0"');

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('current')->nullable(false)->unique()->change();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('current');
            $table->renameColumn('min', 'version');
        });
    }
}
