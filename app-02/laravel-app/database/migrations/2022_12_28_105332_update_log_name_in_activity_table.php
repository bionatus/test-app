<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateLogNameInActivityTable extends Migration
{
    const TABLE_NAME = 'activity_log';

    public function up()
    {
        DB::statement("UPDATE " . self::TABLE_NAME . " SET log_name  = 'forum' WHERE log_name  = 'default'");
    }

    public function down()
    {
        DB::statement("UPDATE " . self::TABLE_NAME . " SET log_name  = 'default' WHERE log_name  = 'forum'");
    }
}
