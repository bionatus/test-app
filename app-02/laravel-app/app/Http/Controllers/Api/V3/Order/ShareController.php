<?php

namespace App\Http\Controllers\Api\V3\Order;

use App;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SharedOrder;
use Response;

class ShareController extends Controller
{
    public function __invoke(Order $order)
    {
        SharedOrder::create([
            'user_id'  => $order->user_id,
            'order_id' => $order->getKey(),
        ]);

        return Response::noContent();
    }
}
