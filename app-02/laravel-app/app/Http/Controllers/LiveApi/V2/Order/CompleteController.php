<?php

namespace App\Http\Controllers\LiveApi\V2\Order;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Constants\RequestKeys;
use App\Events\Order\Completed;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V2\Order\Complete\InvokeRequest;
use App\Http\Resources\LiveApi\V2\Order\DetailedResource;
use App\Models\Order;
use App\Models\Substatus;
use Symfony\Component\HttpFoundation\Response;

class CompleteController extends Controller
{
    public function __invoke(InvokeRequest $request, Order $order)
    {
        if ($order->orderDelivery->isPickup()) {
            $order->total = $request->get(RequestKeys::TOTAL);
            $order->save();
        }

        $order = App::make(ChangeStatus::class, ['order' => $order, 'substatusId' => Substatus::STATUS_COMPLETED_DONE])
            ->execute();

        Completed::dispatch($order);

        return (new DetailedResource($order))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
