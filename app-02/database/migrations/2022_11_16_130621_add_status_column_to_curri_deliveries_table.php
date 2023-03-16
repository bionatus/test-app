<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusColumnToCurriDeliveriesTable extends Migration
{
    const TABLE_NAME = 'curri_deliveries';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('status')->nullable()->after('tracking_id');
            $table->index('book_id');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('status');
            $table->dropIndex('curri_deliveries_book_id_index');
        });
    }
}
