<?php

namespace App\Http\Controllers\Api\V3\Order\Delivery\Curri;

use App;
use App\Actions\Models\Order\Delivery\Curri\LegacyBook;
use App\Constants\RequestKeys;
use App\Events\Order\Delivery\Curri\Booked;
use App\Events\Order\Delivery\Curri\ConfirmedByUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Order\Delivery\Curri\Confirm\InvokeRequest;
use App\Http\Resources\Api\V3\Order\BaseResource;
use App\Models\Order;
use DB;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class ConfirmController extends Controller
{
    /**
     * @throws Throwable
     */
    public function __invoke(InvokeRequest $request, Order $order)
    {
        $orderDelivery     = $order->orderDelivery;
        $date              = $request->get(RequestKeys::DATE);
        $timezone          = $order->supplier->timezone;
        $deliveryStartTime = $request->get(RequestKeys::START_TIME);
        $deliveryEndTime   = $request->get(RequestKeys::END_TIME);
        $startDateTime     = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $deliveryStartTime, $timezone);

        if ($startDateTime->lte(Carbon::now()) && $startDateTime->addMinutes(150)->gte(Carbon::now())) {
            $deliveryStartTime = Carbon::now($timezone)->startOfHour()->format('H:i');
        }

        $orderDelivery->requested_date       = $date;
        $orderDelivery->requested_start_time = $deliveryStartTime;
        $orderDelivery->requested_end_time   = $deliveryEndTime;
        $orderDelivery->date                 = $date;
        $orderDelivery->start_time           = $deliveryStartTime;
        $orderDelivery->end_time             = $deliveryEndTime;

        $order = DB::transaction(function() use ($order, $orderDelivery) {
            $orderDelivery->save();

            return App::make(LegacyBook::class, ['order' => $order])->execute();
        });

        Booked::dispatch($order);
        ConfirmedByUser::dispatch($order);

        return (new BaseResource($order))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }
}
