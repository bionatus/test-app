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

class RemoveUserDeliveryInformation implements ShouldQueue
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
        $database     = App::make('firebase.database');
        $databaseNode = Config::get('mobile.firebase.order_delivery_node');
        $key          = $databaseNode . $this->order->user->getKey() . DIRECTORY_SEPARATOR . $this->order->getRouteKey();

        $database->getReference($key)->remove();
    }
}
