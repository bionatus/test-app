<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrackingLinkColumnToCurriDeliveriesTable extends Migration
{
    const TABLE_NAME = 'curri_deliveries';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('tracking_id')->nullable()->after('book_id');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('tracking_id');
        });
    }
}
