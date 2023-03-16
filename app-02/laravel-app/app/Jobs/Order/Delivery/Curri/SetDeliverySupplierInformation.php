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

class SetDeliverySupplierInformation implements ShouldQueue
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
        $order          = $this->order;
        $user           = $this->order->user;
        $supplier       = $this->order->supplier;
        $orderDelivery  = $order->orderDelivery;
        $totalLineItems = $order->activeItemOrders()->count();
        $database       = App::make('firebase.database');
        $databaseNode   = Config::get('live.firebase.order_delivery_node');
        $key            = $databaseNode . $supplier->getRouteKey() . DIRECTORY_SEPARATOR . $order->getRouteKey();
        $value          = [
            'po'               => $order->name,
            'bid'              => $order->bid_number,
            'company_name'     => $user->companyName(),
            'user_name'        => $user->fullName(),
            'total_line_items' => $totalLineItems,
            'date'             => $orderDelivery->date->format('Y-m-d'),
            'time'             => $orderDelivery->time_range,
        ];

        $database->getReference()->update([$key => $value]);
    }
}
