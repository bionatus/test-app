<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropStatusColumnToOrdersTable extends Migration
{
    const TABLE_NAME     = 'orders';
    const STATUS_PENDING = 'pending';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('status', 25)->default(self::STATUS_PENDING);
        });

        Order::query()->cursor()->each(function(Order $order) {
            $currentSubStatus = $order->orderSubstatuses()->latest()->first();

            $order->status = $currentSubStatus->substatus->status->name;
            $order->save();
        });
    }
}
