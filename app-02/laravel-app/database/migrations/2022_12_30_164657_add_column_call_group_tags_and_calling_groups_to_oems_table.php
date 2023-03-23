<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCallGroupTagsAndCallingGroupsToOemsTable extends Migration
{
    const TABLE_NAME = 'oems';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->string('call_group_tags')->nullable()->after('syncing_notes');
            $table->string('calling_groups')->nullable()->after('call_group_tags');
        });
    }

    public function down()
    {
        Schema::table('oems', function (Blueprint $table) {
            $table->dropColumn('call_group_tags');
            $table->dropColumn('calling_groups');
        });
    }
}
