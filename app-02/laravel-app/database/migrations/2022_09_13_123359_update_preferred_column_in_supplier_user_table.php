<?php

use Illuminate\Database\Migrations\Migration;

class UpdatePreferredColumnInSupplierUserTable extends Migration
{
    const SQLITE      = 'sqlite';
    const TABLE_NAME  = 'supplier_user';
    const COLUMN_NAME = 'preferred';

    public function up()
    {
        if (DB::connection()->getName() === self::SQLITE) {
            return;
        }

        DB::statement('ALTER TABLE ' . self::TABLE_NAME . ' MODIFY COLUMN ' . self::COLUMN_NAME . ' BOOLEAN NULL AFTER cash_buyer');
    }

    public function down()
    {
        if (DB::connection()->getName() === self::SQLITE) {
            return;
        }

        DB::statement('ALTER TABLE ' . self::TABLE_NAME . ' MODIFY COLUMN ' . self::COLUMN_NAME . ' BOOLEAN NULL AFTER updated_at');
    }
}
