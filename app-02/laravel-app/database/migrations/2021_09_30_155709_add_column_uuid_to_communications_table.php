<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnUuidToCommunicationsTable extends Migration
{
    const TABLE_NAME = 'communications';

    public function up()
    {
        if ('sqlite' === DB::connection()->getName()) {
            $this->upSqlite();

            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->char('uuid', 36)->unique()->after('session_id');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }

    public function upSqlite()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->char('uuid', 36)->unique()->after('session_id')->default('');
        });
    }
}
