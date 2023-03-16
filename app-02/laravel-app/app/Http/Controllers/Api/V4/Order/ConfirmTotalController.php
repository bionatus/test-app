<?php

namespace App\Http\Controllers\Api\V4\Order;

use App;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V4\Order\ConfirmTotal\StoreRequest;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use DB;
use Symfony\Component\HttpFoundation\Response;

class ConfirmTotalController extends Controller
{
    /**
     * @throws \Throwable
     */
    public function __invoke(StoreRequest $request, Order $order)
    {
        DB::transaction(function() use ($request, $order) {
            $order->paid_total = $request->get(RequestKeys::PAID_TOTAL);
            $order->save();

            $orderSubStatus               = App::make(OrderSubstatus::class);
            $orderSubStatus->order_id     = $order->getKey();
            $orderSubStatus->substatus_id = Substatus::STATUS_APPROVED_DELIVERED;
            $orderSubStatus->save();
        });

        return (new BaseResource($order->fresh()))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
