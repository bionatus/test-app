<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortAndPublishedAtToXoxoVouchersTable extends Migration
{
    const TABLE_NAME = 'xoxo_vouchers';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->integer('sort')->nullable()->after('terms_conditions');
            $table->timestamp('published_at')->nullable()->after('sort');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('sort');
            $table->dropColumn('published_at');
        });
    }
}
