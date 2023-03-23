<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastMessageDateColumnsToPubnubChannelsTable extends Migration
{
    const TABLE_NAME = 'pubnub_channels';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('user_last_message_at')->nullable()->after('channel');
            $table->string('supplier_last_message_at')->nullable()->after('channel');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('user_last_message_at');
            $table->dropColumn('supplier_last_message_at');
        });
    }
}
