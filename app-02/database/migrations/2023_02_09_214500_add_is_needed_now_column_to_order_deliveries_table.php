
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsNeededNowColumnToOrderDeliveriesTable extends Migration
{
    const TABLE_NAME = 'order_deliveries';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->boolean('is_needed_now')->nullable()->after('fee');
        });

    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('is_needed_now');
        });
    }
}
