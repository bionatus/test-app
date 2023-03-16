<?php

use App\Models\OrderDelivery;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AddColumnStartTimeAndEndTimeToOrderDeliveryTable extends Migration
{
    const TABLE_NAME = 'order_deliveries';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->time('end_time')->nullable()->after('time');
            $table->time('start_time')->nullable()->after('time');
        });

        /** @var OrderDelivery[] $order_deliveries */
        $order_deliveries = OrderDelivery::all();
        foreach ($order_deliveries as $order_delivery) {
            if (!$order_delivery->time) {
                continue;
            }

            $timeArray = explode(' - ', $order_delivery->time);
            if (2 != count($timeArray)) {
                continue;
            }

            $old_start_time = $timeArray[0];
            $old_end_time   = $timeArray[1];

            $new_start_time = Carbon::create($old_start_time)->format('H:i:s');
            $new_end_time   = Carbon::create($old_end_time)->format('H:i:s');

            $order_delivery->start_time = $new_start_time;
            $order_delivery->end_time   = $new_end_time;

            $order_delivery->save();
        }

        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('time');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->string('time')->nullable()->after('date');
        });

        /** @var OrderDelivery $order_delivery */
        $order_deliveries = OrderDelivery::all();
        foreach ($order_deliveries as $order_delivery) {
            if (!$order_delivery->start_time || is_null($order_delivery->start_time))
                continue;

            $old_start_time = Carbon::create($order_delivery->start_time)->format('gA');
            $old_end_time = Carbon::create($order_delivery->end_time)->format('gA');

            $new_time = $old_start_time . ' - ' . $old_end_time;

            $order_delivery->time = $new_time;

            $order_delivery->save();
        }

        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('end_time');
            $table->dropColumn('start_time');
        });

    }
}
