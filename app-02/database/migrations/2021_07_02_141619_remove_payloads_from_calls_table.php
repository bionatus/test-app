<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePayloadsFromCallsTable extends Migration
{
    const TABLE_NAME = 'calls';

    public function up()
    {
        if ('sqlite' === DB::connection()->getName()) {
            $this->upSqlite();

            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable()->change();
            $table->dropColumn('payloads');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->addColumn('json', 'payloads')->after('status');
        });
    }

    public function upSqlite(): void
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('payloads');
        });
    }
}
