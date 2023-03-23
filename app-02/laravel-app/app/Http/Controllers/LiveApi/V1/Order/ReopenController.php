<?php

namespace App\Http\Controllers\LiveApi\V1\Order;

use App;
use App\Events\Order\Reopen;
use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\Order\Unprocessed\BaseResource;
use App\Models\Order;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\OrderSubstatus;
use App\Models\Scopes\BySupplier;
use App\Models\Scopes\ByUser;
use App\Models\Substatus;
use Symfony\Component\HttpFoundation\Response;

class ReopenController extends Controller
{
    public function store(Order $order)
    {
        $orderSubStatus               = App::make(OrderSubstatus::class);
        $orderSubStatus->order_id     = $order->getKey();
        $orderSubStatus->substatus_id = Substatus::STATUS_PENDING_REQUESTED;
        $orderSubStatus->save();

        Reopen::dispatch($order);

        $userOldestPendingOrder = Order::scoped(new BySupplier($order->supplier))
            ->scoped(new ByUser($order->user))
            ->scoped(new ByLastSubstatuses([Substatus::STATUS_PENDING_REQUESTED]))
            ->oldest(Order::CREATED_AT)
            ->first();
        $order->user->setAttribute('oldestPendingOrder', $userOldestPendingOrder);

        return (new BaseResource($order))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
