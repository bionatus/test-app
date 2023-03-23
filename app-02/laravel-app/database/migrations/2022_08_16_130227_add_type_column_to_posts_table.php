<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnToPostsTable extends Migration
{
    const TABLE_NAME = 'posts';
    const TYPE_OTHER = 'other';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('type')->after('message')->default(self::TYPE_OTHER);
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
