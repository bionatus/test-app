<?php

namespace App\Http\Controllers\Api\V4\Order;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Events\Order\Completed;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Models\Order;
use App\Models\Substatus;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompleteController extends Controller
{
    public function __invoke(Request $request, Order $order)
    {
        $order = App::make(ChangeStatus::class, ['order' => $order, 'substatusId' => Substatus::STATUS_COMPLETED_DONE])
            ->execute();

        Completed::dispatch($order);

        return (new BaseResource($order))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
