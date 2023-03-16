<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdColumnToOemDetailCounter extends Migration
{
    const TABLE_NAME = 'oem_detail_counter';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('staff_id')->nullable()->change();
            $table->foreignId('user_id')
                ->type('integer')
                ->unsigned()
                ->nullable()
                ->after('staff_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('staff_id')->change();
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}
