<?php

namespace App\Http\Controllers\LiveApi\V1\Unauthenticated;

use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\Unauthenticated\Order\BaseResource;
use App\Models\Order;

class OrderController extends Controller
{
    public function show(Order $order)
    {
        return new BaseResource($order);
    }
}
