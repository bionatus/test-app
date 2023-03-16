<?php

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use App\Models\WarehouseDelivery;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingColumnsToOrderDeliveriesTable extends Migration
{
    const TABLE_NAME = 'order_deliveries';

    public function up()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->string('type')->nullable()->after('order_id');
            $table->date('requested_date')->nullable()->after('type');
            $table->string('requested_time')->nullable()->after('requested_date');
            $table->date('date')->nullable()->after('requested_time');
            $table->string('time')->nullable()->after('date');
            $table->string('note')->nullable()->after('time');
            $table->datetime('supplier_confirmed_at')->nullable()->after('note');
            $table->datetime('user_confirmed_at')->nullable()->after('supplier_confirmed_at');
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->unique(['order_id']);
        });

        Order::query()->cursor()->each(function(Order $order) {
            $date = ('asap' == $order->requested_availability) ? $order->created_at : $order->created_at->addDay();

            $type         = OrderDelivery::TYPE_PICKUP;
            $deliveryType = new Pickup();

            $orderDeliveryExists = $order->orderDelivery;
            if ($orderDeliveryExists) {
                $type         = OrderDelivery::TYPE_WAREHOUSE_DELIVERY;
                $deliveryType = new WarehouseDelivery();
            }

            $requestedDeliveryAddress = $order->requested_delivery_address;
            if ($orderDeliveryExists && $requestedDeliveryAddress) {
                $address = Address::create(['address_1' => $requestedDeliveryAddress]);

                $deliveryType->destination_address_id = $address->getKey();
            }

            $orderDelivery = $order->orderDelivery()->updateOrCreate(['order_id' => $order->getKey()], [
                'type'           => $type,
                'requested_date' => $date,
                'requested_time' => '9AM - 12PM',
                'date'           => $date,
                'time'           => '9AM - 12PM',
            ]);

            $deliveryType->id = $orderDelivery->getKey();
            $deliveryType->save();
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn('eta');
        });
    }

    public function down()
    {
        if ('sqlite' === DB::connection()->getName()) {
            $this->downSqlite();

            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->integer('eta')->nullable();
            $table->dropColumn('type');
            $table->dropColumn('requested_date');
            $table->dropColumn('requested_time');
            $table->dropColumn('date');
            $table->dropColumn('time');
            $table->dropColumn('note');
            $table->dropColumn('supplier_confirmed_at');
            $table->dropColumn('user_confirmed_at');

            $table->dropForeign(['order_id']);
            $table->dropUnique(['order_id']);

            $table->foreign('order_id')->references('id')->on('orders')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function downSqlite()
    {
        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->dropColumn(['order_id']);
        });

        Schema::table(self::TABLE_NAME, function(Blueprint $table) {
            $table->foreignId('order_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
}
