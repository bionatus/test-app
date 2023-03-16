<?php

namespace App\Http\Controllers\LiveApi\V1\Order;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Order\Fee\StoreRequest;
use App\Http\Resources\LiveApi\V1\Order\BaseResource;
use App\Models\Order;
use Symfony\Component\HttpFoundation\Response as ResponseHttp;

class FeeController extends Controller
{
    public function store(StoreRequest $request, Order $order)
    {
        $order->discount = $request->get(RequestKeys::DISCOUNT) ?? Order::DEFAULT_DISCOUNT;
        $order->tax      = $request->get(RequestKeys::TAX) ?? Order::DEFAULT_TAX;
        $order->save();

        return (new BaseResource($order))->response()->setStatusCode(ResponseHttp::HTTP_CREATED);
    }
}
