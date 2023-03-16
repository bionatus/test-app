<?php

namespace App\Http\Controllers\LiveApi\V1\Order\InProgress\Delivery\Curri;

use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\Order\BaseResource;
use App\Models\CurriDelivery;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfirmController extends Controller
{
    public function __invoke(Request $request, Order $order)
    {
        /** @var CurriDelivery $curriDelivery */
        $curriDelivery = $order->orderDelivery->deliverable;

        $curriDelivery->supplier_confirmed_at = Carbon::now();
        $curriDelivery->save();

        return (new BaseResource($order))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
