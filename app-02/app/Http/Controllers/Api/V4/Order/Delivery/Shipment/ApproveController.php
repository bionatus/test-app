<?php

namespace App\Http\Controllers\Api\V4\Order\Delivery\Shipment;

use App;
use App\Events\Order\Approved;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use Symfony\Component\HttpFoundation\Response;

class ApproveController extends Controller
{
    public function __invoke(Order $order)
    {
        $orderSubStatus               = App::make(OrderSubstatus::class);
        $orderSubStatus->order_id     = $order->getKey();
        $orderSubStatus->substatus_id = Substatus::STATUS_APPROVED_AWAITING_DELIVERY;
        $orderSubStatus->save();

        Approved::dispatch($order);

        return (new BaseResource($order->fresh()))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
