<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestedAvailabilityToOrdersTable extends Migration
{
    const TABLE_NAME                     = 'orders';
    const COLUMN_NAME                    = 'requested_availability';
    const DEFAULT_REQUESTED_AVAILABILITY = 'asap';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string(self::COLUMN_NAME, 25)->default(self::DEFAULT_REQUESTED_AVAILABILITY)->after('bid_number');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn(self::COLUMN_NAME);
        });
    }
}
