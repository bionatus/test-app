<?php

namespace App\Http\Controllers\Api\V3\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Order\ItemOrder\BaseResource;
use App\Models\Order;

class ItemOrderController extends Controller
{
    public function index(Order $order)
    {
        $page = $order->activeItemOrders()->with(['item.orderable', 'replacement.singleReplacement.part'])->paginate();

        return BaseResource::collection($page);
    }
}
