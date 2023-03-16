<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUserDeleteCascadeToOrdersTable extends Migration
{
    const TABLE_NAME = 'orders';
    const SQLITE     = 'sqlite';

    public function up()
    {
        if (DB::connection()->getName() === self::SQLITE) {
            $this->upSqlite();

            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->integer('user_id')->unsigned()->nullable()->change();
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    private function upSqlite()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('user_id');
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('user_id')
                ->type('integer')
                ->nullable()
                ->unsigned()
                ->constrained()
                ->cascadeOnUpdate()
                ->onDelete('SET NULL');
        });
    }
}
