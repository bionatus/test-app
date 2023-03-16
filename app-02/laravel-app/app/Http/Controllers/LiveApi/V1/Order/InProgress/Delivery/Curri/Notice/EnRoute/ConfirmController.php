<?php

namespace App\Http\Controllers\LiveApi\V1\Order\InProgress\Delivery\Curri\Notice\EnRoute;

use App\Events\Order\Delivery\Curri\Notice\EnRoute\ConfirmedBySupplier;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ConfirmController extends Controller
{
    public function __invoke(Order $order)
    {
        ConfirmedBySupplier::dispatch($order);

        return Response::noContent()->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
