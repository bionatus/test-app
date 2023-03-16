<?php

namespace App\Http\Controllers\LiveApi\V1\Unauthenticated\Order;

use App;
use App\Actions\Models\Order\Approve;
use App\Constants\RequestKeys;
use App\Events\Order\ApprovedByTeam;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Unauthenticated\Order\Approve\InvokeRequest;
use App\Http\Resources\LiveApi\V1\Unauthenticated\Order\BaseResource;
use App\Models\Order;
use Symfony\Component\HttpFoundation\Response;

class ApproveController extends Controller
{
    public function __invoke(InvokeRequest $request, Order $order)
    {
        $name  = $request->get(RequestKeys::NAME);
        $order = App::make(Approve::class, ['name' => $name, 'order' => $order])->execute();

        ApprovedByTeam::dispatch($order);

        return (new BaseResource($order))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
