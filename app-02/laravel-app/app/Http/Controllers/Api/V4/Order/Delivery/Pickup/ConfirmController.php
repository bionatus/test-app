<?php

namespace App\Http\Controllers\Api\V4\Order\Delivery\Pickup;

use App;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ConfirmController extends Controller
{
    public function __invoke(Order $order)
    {
        $orderSubstatus               = App::make(OrderSubstatus::class);
        $orderSubstatus->order_id     = $order->getKey();
        $orderSubstatus->substatus_id = Substatus::STATUS_APPROVED_DELIVERED;
        $orderSubstatus->save();

        return (new BaseResource($order->fresh()))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }
}
