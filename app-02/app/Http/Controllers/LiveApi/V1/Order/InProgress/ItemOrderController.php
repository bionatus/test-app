<?php

namespace App\Http\Controllers\LiveApi\V1\Order\InProgress;

use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\Order\InProgress\ItemOrder\BaseResource;
use App\Models\Order;

class ItemOrderController extends Controller
{
    public function index(Order $order)
    {
        $page = ($order->isCanceled() ? $order->ItemOrders() : $order->availableAndRemovedItemOrders())->with([
            'item.orderable',
            'replacement.singleReplacement.part',
        ])->paginate();

        return BaseResource::collection($page);
    }
}
