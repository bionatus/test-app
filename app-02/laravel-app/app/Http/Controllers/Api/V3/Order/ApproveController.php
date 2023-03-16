<?php

namespace App\Http\Controllers\Api\V3\Order;

use App;
use App\Actions\Models\Order\Approve;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Order\Approve\InvokeRequest;
use App\Http\Resources\Api\V3\Order\BaseResource;
use App\Models\Order;
use Symfony\Component\HttpFoundation\Response;

class ApproveController extends Controller
{
    public function __invoke(InvokeRequest $request, Order $order)
    {
        $name  = $request->get(RequestKeys::NAME);
        $order = App::make(Approve::class, ['name' => $name, 'order' => $order])->execute();

        return (new BaseResource($order))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
