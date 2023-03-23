<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalAndTotalPaidColumnsToOrdersTable extends Migration
{
    const TABLE_NAME = 'orders';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->unsignedBigInteger('total')->after('bid_number')->nullable();
            $table->unsignedBigInteger('paid_total')->after('total')->nullable();
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('total');
            $table->dropColumn('paid_total');
        });
    }
}
