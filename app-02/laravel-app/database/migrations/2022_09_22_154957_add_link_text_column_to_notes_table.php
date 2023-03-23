<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLinkTextColumnToNotesTable extends Migration
{
    const TABLE_NAME = 'notes';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('link')->nullable()->change();
            $table->string('link_text')->nullable()->after('link');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('link_text');
        });
    }
}
