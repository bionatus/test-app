<?php

namespace App\Http\Controllers\LiveApi\V1\Order;

use App;
use App\Events\Order\LegacyCompleted;
use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompleteController extends Controller
{
    public function __invoke(Request $request, Order $order)
    {
        $orderSubStatus               = App::make(OrderSubstatus::class);
        $orderSubStatus->order_id     = $order->getKey();
        $orderSubStatus->substatus_id = Substatus::STATUS_COMPLETED_DONE;
        $orderSubStatus->save();

        LegacyCompleted::dispatch($order);

        return (new BaseResource($order))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
