<?php

namespace App\Http\Controllers\Api\V4\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V4\Order\ItemOrder\BaseResource;
use App\Models\Order;

class ItemOrderController extends Controller
{
    public function index(Order $order)
    {
        $page = $order->activeItemOrders()->with([
            'item.orderable.item',
            'replacement.note',
            'replacement.singleReplacement.part.note',
            'replacement.singleReplacement.part.item',
        ])->paginate();

        return BaseResource::collection($page);
    }
}
