<?php

use App\Models\OrderDelivery;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AddColumnRequestedStartTimeAndRequestedEndTimeToOrderDeliveryTable extends Migration
{
    const TABLE_NAME = 'order_deliveries';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->time('requested_end_time')->nullable()->after('requested_time');
            $table->time('requested_start_time')->nullable()->after('requested_time');
        });

        /** @var OrderDelivery[] $order_deliveries */
        $order_deliveries = OrderDelivery::all();
        foreach ($order_deliveries as $order_delivery) {
            if (!$order_delivery->requested_time) {
                continue;
            }

            $timeArray = explode(' - ', $order_delivery->requested_time);
            if (2 != count($timeArray)) {
                continue;
            }

            $old_requested_start_time = $timeArray[0];
            $old_requested_end_time   = $timeArray[1];

            $new_requested_start_time = Carbon::create($old_requested_start_time)->format('H:i:s');
            $new_requested_end_time   = Carbon::create($old_requested_end_time)->format('H:i:s');

            $order_delivery->requested_start_time = $new_requested_start_time;
            $order_delivery->requested_end_time   = $new_requested_end_time;

            $order_delivery->save();
        }

        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('requested_time');
        });
    }

    public function down()
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->string('requested_time')->nullable()->after('requested_date');
        });

        /** @var OrderDelivery $order_delivery */
        $order_deliveries = OrderDelivery::all();
        foreach ($order_deliveries as $order_delivery) {
            if (!$order_delivery->requested_start_time || is_null($order_delivery->requested_start_time))
                continue;

            $old_requested_start_time = Carbon::create($order_delivery->requested_start_time)->format('gA');
            $old_requested_end_time = Carbon::create($order_delivery->requested_end_time)->format('gA');

            $new_requested_time = $old_requested_start_time . ' - ' . $old_requested_end_time;

            $order_delivery->requested_time = $new_requested_time;

            $order_delivery->save();
        }

        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('requested_end_time');
            $table->dropColumn('requested_start_time');
        });
    }
}
