<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveTokenColumnsFromPubnubChannelsTable extends Migration
{
    const TABLE_NAME = 'pubnub_channels';
    const SQLITE     = 'sqlite';

    public function up()
    {
        if (DB::connection()->getName() === self::SQLITE) {
            $this->upSqlite();

            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            if (Schema::hasColumn(self::TABLE_NAME, 'user_token')) {
                $table->dropColumn('user_token');
            }

            if (Schema::hasColumn(self::TABLE_NAME, 'supplier_token')) {
                $table->dropColumn('supplier_token');
            }

            if (Schema::hasColumn(self::TABLE_NAME, 'user_token_valid_until')) {
                $table->dropColumn('user_token_valid_until');
            }

            if (Schema::hasColumn(self::TABLE_NAME, 'supplier_token_valid_until')) {
                $table->dropColumn('supplier_token_valid_until');
            }
        });
    }

    public function upSqlite()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            if (Schema::hasColumn(self::TABLE_NAME, 'user_token')) {
                $table->dropColumn('user_token');
            }
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            if (Schema::hasColumn(self::TABLE_NAME, 'supplier_token')) {
                $table->dropColumn('supplier_token');
            }
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            if (Schema::hasColumn(self::TABLE_NAME, 'user_token_valid_until')) {
                $table->dropColumn('user_token_valid_until');
            }
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            if (Schema::hasColumn(self::TABLE_NAME, 'supplier_token_valid_until')) {
                $table->dropColumn('supplier_token_valid_until');
            }
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->timestamp('supplier_token_valid_until')->nullable()->after('channel');
            $table->timestamp('user_token_valid_until')->nullable()->after('channel');
            $table->text('supplier_token')->nullable()->after('channel');
            $table->text('user_token')->nullable()->after('channel');
        });
    }
}
