<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsFromOrdersTable extends Migration
{
    const TABLE_NAME = 'orders';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('requested_availability');
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('availability');
        });
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('requested_delivery_address');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('requested_availability', 25)->default('asap')->after('bid_number');
            $table->string('availability', 50)->nullable()->after('requested_availability');
            $table->string('requested_delivery_address')->nullable()->after('availability');
        });
    }
}
