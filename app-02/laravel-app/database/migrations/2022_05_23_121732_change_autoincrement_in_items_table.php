<?php

use Illuminate\Database\Migrations\Migration;

class ChangeAutoincrementInItemsTable extends Migration
{
    const TABLE_NAME = 'items';
    const MYSQL      = 'mysql';

    public function up()
    {
        if (DB::connection()->getName() === self::MYSQL) {
            DB::statement("ALTER TABLE " . self::TABLE_NAME . " AUTO_INCREMENT = 10000000;");
        }
    }

    public function down()
    {
        if (DB::connection()->getName() === self::MYSQL) {
            $count = DB::table(self::TABLE_NAME)->max('id') + 1;
            DB::statement("ALTER TABLE " . self::TABLE_NAME . " AUTO_INCREMENT = " . $count);
        }
    }
}
