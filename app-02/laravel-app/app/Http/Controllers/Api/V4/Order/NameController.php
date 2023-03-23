<?php

namespace App\Http\Controllers\Api\V4\Order;

use App;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V4\Order\Name\InvokeRequest;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Models\Order;
use Symfony\Component\HttpFoundation\Response;

class NameController extends Controller
{
    public function __invoke(InvokeRequest $request, Order $order)
    {
        $order->name = $request->get(RequestKeys::NAME);
        $order->save();

        return (new BaseResource($order))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
