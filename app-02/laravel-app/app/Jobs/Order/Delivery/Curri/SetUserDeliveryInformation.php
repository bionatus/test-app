<?php

namespace App\Jobs\Order\Delivery\Curri;

use App;
use App\Models\Order;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetUserDeliveryInformation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Order $order;

    public function __construct(Order $order)
    {
        $this->onConnection('database');

        $this->order = $order;
    }

    public function handle()
    {
        $order              = $this->order;
        $supplier           = $order->supplier;
        $orderDelivery      = $order->orderDelivery;
        $destinationAddress = $orderDelivery->deliverable->destinationAddress;

        $database     = App::make('firebase.database');
        $databaseNode = Config::get('mobile.firebase.order_delivery_node');
        $key          = $databaseNode . $order->user->getKey() . DIRECTORY_SEPARATOR . $order->getRouteKey();

        $value = [
            'po'                    => $order->name,
            'supplier_name'         => $supplier->name,
            'date'                  => $orderDelivery->date->format('Y-m-d'),
            'time'                  => $orderDelivery->time_range,
            'destination_address'   => $destinationAddress->address_1,
            'destination_address_2' => $destinationAddress->address_2,
            'destination_state'     => $destinationAddress->state,
            'destination_city'      => $destinationAddress->city,
            'destination_zip'       => $destinationAddress->zip_code,
        ];

        $database->getReference()->update([$key => $value]);
    }
}
