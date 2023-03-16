<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsFromOrderSubstatusTable extends Migration
{
    const TABLE_NAME = 'order_substatus';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn(['name', 'sub_status']);
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('name', 25)->nullable();
            $table->string('sub_status', 25)->nullable();
        });
    }
}
