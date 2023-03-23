<?php

namespace App\Http\Controllers\LiveApi\V1\Order\Delivery;

use App\Constants\RequestKeys;
use App\Events\Order\DeliveryEtaUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Order\Delivery\UpdateEta\InvokeRequest;
use App\Http\Resources\LiveApi\V1\Order\Delivery\BaseResource;
use App\Models\Order;

class UpdateEtaController extends Controller
{
    public function __invoke(InvokeRequest $request, Order $order)
    {
        $delivery = $order->orderDelivery;

        $delivery->date       = $request->get(RequestKeys::DATE);
        $delivery->start_time = $request->get(RequestKeys::START_TIME);
        $delivery->end_time   = $request->get(RequestKeys::END_TIME);
        $delivery->save();

        DeliveryEtaUpdated::dispatch($order);

        return (new BaseResource($delivery));
    }
}
