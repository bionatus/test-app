<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnToTipsTable extends Migration
{
    const TABLE_NAME = 'tips';
    const SQLITE     = 'sqlite';

    public function up()
    {
        if (DB::connection()->getName() === self::SQLITE) {
            $this->upSqlite();

            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->string('type', 255)->nullable()->after('description');
        });
    }

    public function upSqlite()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('type', 255)->nullable()->after('description');
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            if (Schema::hasColumn(self::TABLE_NAME, 'created_at')) {
                $table->dropColumn('created_at');
            }
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            if (Schema::hasColumn(self::TABLE_NAME, 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->timestamps();
            $table->dropColumn('type');
        });
    }
}
