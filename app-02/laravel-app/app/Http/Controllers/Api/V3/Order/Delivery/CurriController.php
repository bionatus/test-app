<?php

namespace App\Http\Controllers\Api\V3\Order\Delivery;

use App;
use App\Actions\Models\Order\Delivery\Curri\LegacyCalculateJobExecutionTime;
use App\Constants\RequestKeys;
use App\Events\Order\Delivery\Curri\ConfirmedByUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Order\Delivery\Curri\UpdateRequest;
use App\Http\Resources\Api\V3\Order\BaseResource;
use App\Jobs\Order\Delivery\Curri\LegacyDelayBooking as DelayBookingJob;
use App\Models\Order;
use App\Services\Curri\Curri;
use App\Types\Money;
use DB;
use Illuminate\Support\Carbon;

class CurriController extends Controller
{
    /**
     * @throws \Throwable
     */
    public function update(UpdateRequest $request, Order $order)
    {
        $orderDelivery = $order->orderDelivery;
        $curriDelivery = $orderDelivery->deliverable;
        $address       = $curriDelivery->destinationAddress;

        $date              = $request->get(RequestKeys::DATE);
        $timezone          = $order->supplier->timezone;
        $deliveryStartTime = $request->get(RequestKeys::START_TIME);
        $deliveryEndTime   = $request->get(RequestKeys::END_TIME);
        $startDateTime     = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $deliveryStartTime, $timezone);

        $address->address_1 = $request->get(RequestKeys::ADDRESS);
        $address->address_2 = $request->get(RequestKeys::ADDRESS_2);
        $address->country   = $request->get(RequestKeys::COUNTRY);
        $address->state     = $request->get(RequestKeys::STATE);
        $address->zip_code  = $request->get(RequestKeys::ZIP_CODE);
        $address->city      = $request->get(RequestKeys::CITY);

        if ($startDateTime->lte(Carbon::now()) && $startDateTime->addMinutes(150)->gte(Carbon::now())) {
            $deliveryStartTime = Carbon::now($timezone)->startOfHour()->format('H:i');
        }

        $orderDelivery->note                 = $request->get(RequestKeys::NOTE);
        $orderDelivery->requested_date       = $date;
        $orderDelivery->requested_start_time = $deliveryStartTime;
        $orderDelivery->requested_end_time   = $deliveryEndTime;
        $orderDelivery->date                 = $date;
        $orderDelivery->start_time           = $deliveryStartTime;
        $orderDelivery->end_time             = $deliveryEndTime;

        $quote = App::make(Curri::class)
            ->getQuote($address, $curriDelivery->originAddress, $curriDelivery->vehicle_type);

        $curriDelivery->quote_id = $quote['quoteId'];
        $orderDelivery->fee      = Money::toDollars($quote['fee']);

        DB::transaction(function() use ($address, $orderDelivery, $curriDelivery) {
            $address->save();
            $curriDelivery->save();
            $orderDelivery->save();
        });

        $delayTime = App::make(LegacyCalculateJobExecutionTime::class, ['order' => $order])->execute();
        DelayBookingJob::dispatch($order)->delay($delayTime);

        ConfirmedByUser::dispatch($order);

        return new BaseResource($order);
    }
}
