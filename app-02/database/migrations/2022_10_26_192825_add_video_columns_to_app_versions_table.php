<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVideoColumnsToAppVersionsTable extends Migration
{
    const TABLE_NAME = 'app_versions';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('video_title')->nullable()->after('version');
            $table->string('video_url')->nullable()->after('video_title');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('video_title');
            $table->dropColumn('video_url');
        });
    }
}
