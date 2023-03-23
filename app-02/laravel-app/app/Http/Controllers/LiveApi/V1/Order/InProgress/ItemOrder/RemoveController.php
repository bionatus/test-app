<?php

namespace App\Http\Controllers\LiveApi\V1\Order\InProgress\ItemOrder;

use App\Events\Order\ItemOrder\Removed;
use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\Order\InProgress\ItemOrder\BaseResource;
use App\Models\ItemOrder;
use App\Models\Order;
use Symfony\Component\HttpFoundation\Response;

class RemoveController extends Controller
{
    public function __invoke(Order $order, ItemOrder $itemOrder)
    {
        $itemOrder->status = ItemOrder::STATUS_REMOVED;
        $itemOrder->save();

        Removed::dispatch($itemOrder);

        return (new BaseResource($itemOrder))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
