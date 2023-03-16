<?php

namespace App\Http\Controllers\LiveApi\V1\Order;

use App;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Order\PreApproval\InvokeRequest;
use App\Http\Resources\LiveApi\V1\Order\Unprocessed\BaseResource;
use App\Models\Order;
use Symfony\Component\HttpFoundation\Response;

class PreApprovalController extends Controller
{
    public function __invoke(InvokeRequest $request, Order $order)
    {
        $bidNumber = $request->get(RequestKeys::BID_NUMBER);

        $order->bid_number = $bidNumber;
        $order->save();

        return (new BaseResource($order))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
