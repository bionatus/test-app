<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ModifyTypeOfConnectionTypeFieldContactorsTable extends Migration
{
    const TABLE_NAME  = 'contactors';
    const COLUMN_NAME = 'connection_type';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table){
            $table->string(self::COLUMN_NAME, 255)->change();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table){
            $table->string(self::COLUMN_NAME, 25)->change();
        });
    }
}
