<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnGroupInSettingsTable extends Migration
{
    const TABLE_NAME = 'settings';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('group')->change();
        });
    }

    public function down()
    {
        DB::statement("ALTER TABLE " . self::TABLE_NAME . " CHANGE `group` `group` ENUM('agent','notification') NOT NULL;");
    }
}
