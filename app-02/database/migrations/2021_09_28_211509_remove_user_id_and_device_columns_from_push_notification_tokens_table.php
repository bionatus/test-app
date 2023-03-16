<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUserIdAndDeviceColumnsFromPushNotificationTokensTable extends Migration
{
    const TABLE_NAME = 'push_notification_tokens';

    public function up()
    {
        DB::table(self::TABLE_NAME)->delete();

        if ('sqlite' === DB::connection()->getName()) {
            $this->upSqlite();

            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropColumn('device');
            $table->foreignId('device_id')->unique()->after('id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function upSqlite()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('user_id');
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('device');
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('device_id')
                ->unique()
                ->after('id')
                ->default(0)
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down()
    {
        DB::table(self::TABLE_NAME)->delete();

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->dropColumn('device_id');
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('user_id')
                ->type('integer')
                ->unsigned()
                ->after('id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('device')->after('os')->unique();
        });
    }
}
