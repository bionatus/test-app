<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfirmedAtColumnsToCurriDeliveriesTable extends Migration
{
    const TABLE_NAME = 'curri_deliveries';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->datetime('supplier_confirmed_at')->nullable()->after('book_id');
            $table->datetime('user_confirmed_at')->nullable()->after('supplier_confirmed_at');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('supplier_confirmed_at');
            $table->dropColumn('user_confirmed_at');
        });
    }
}
