<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameStatusesTableToOrderSubstatus extends Migration
{
    const OLD_TABLE_NAME = 'statuses';
    const TABLE_NAME     = 'order_substatus';

    public function up()
    {
        Schema::rename(self::OLD_TABLE_NAME, self::TABLE_NAME);
    }

    public function down()
    {
        Schema::rename(self::TABLE_NAME, self::OLD_TABLE_NAME);
    }
}
