<?php

namespace App\Http\Controllers\LiveApi\V1\Unauthenticated\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\Unauthenticated\Order\ItemOrder\BaseResource;
use App\Models\Order;

class ItemOrderController extends Controller
{
    public function index(Order $order)
    {
        $query = ($order->isCanceled()) ? $order->itemOrders() : $order->availableItemOrders();

        $page = $query->with(['item.orderable', 'replacement.singleReplacement.part'])->paginate();

        return BaseResource::collection($page);
    }
}
