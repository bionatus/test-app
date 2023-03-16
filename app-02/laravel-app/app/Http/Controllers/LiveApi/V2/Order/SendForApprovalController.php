<?php

namespace App\Http\Controllers\LiveApi\V2\Order;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V2\Order\SendForApproval\InvokeRequest;
use App\Http\Resources\LiveApi\V2\Order\DetailedResource;
use App\Models\Order;
use App\Models\Substatus;
use Symfony\Component\HttpFoundation\Response;

class SendForApprovalController extends Controller
{
    public function __invoke(InvokeRequest $request, Order $order)
    {
        $order->total      = $request->get(RequestKeys::TOTAL);
        $order->bid_number = $request->get(RequestKeys::BID_NUMBER);
        $order->note       = $request->get(RequestKeys::NOTE);
        $order->save();

        $order = App::make(ChangeStatus::class,
            ['order' => $order, 'substatusId' => Substatus::STATUS_PENDING_APPROVAL_FULFILLED])->execute();

        return (new DetailedResource($order))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
