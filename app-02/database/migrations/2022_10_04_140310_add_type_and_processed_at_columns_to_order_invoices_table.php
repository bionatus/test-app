<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeAndProcessedAtColumnsToOrderInvoicesTable extends Migration
{
    const TABLE_NAME = 'order_invoices';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('type')->default('invoice')->after('number');
            $table->dateTime('processed_at')->nullable()->after('payment_terms');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('processed_at');
        });
    }
}
