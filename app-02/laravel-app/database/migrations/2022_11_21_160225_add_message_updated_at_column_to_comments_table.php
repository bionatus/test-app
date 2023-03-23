<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMessageUpdatedAtColumnToCommentsTable extends Migration
{
    const TABLE_NAME = 'comments';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dateTime('content_updated_at')->nullable()->after('solution');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('content_updated_at');
        });
    }
}
