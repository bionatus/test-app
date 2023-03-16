<?php

namespace App\Http\Controllers\Api\V4\Order;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Actions\Models\Order\Delivery\CalculateJobExecutionTime;
use App\Actions\Models\Order\Delivery\Curri\Book;
use App\Constants\RequestKeys;
use App\Events\Order\Approved;
use App\Events\Order\Delivery\Curri\Booked;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V4\Order\Approve\InvokeRequest;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Jobs\Order\Delivery\Curri\DelayBooking;
use App\Jobs\Order\Delivery\Pickup\DelayApprovedReadyForDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Substatus;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class ApproveController extends Controller
{
    private const LIMIT_MINUTES = 30;

    /**
     * @throws \Throwable
     */
    public function __invoke(InvokeRequest $request, Order $order)
    {
        $order->name = $request->get(RequestKeys::NAME);
        $order->save();
        if ($order->doesntHavePendingItems() && !$order->orderDelivery->isShipmentDelivery()) {
            $this->updateApproveSubStatus($order);
            Approved::dispatch($order);
        } else {
            App::make(ChangeStatus::class, [
                'order'       => $order,
                'substatusId' => Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
            ])->execute();
        }

        return (new BaseResource($order->fresh()))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    private function updateApproveSubStatus(Order $order): void
    {
        $orderDelivery = $order->orderDelivery;

        if ($orderDelivery->type === OrderDelivery::TYPE_CURRI_DELIVERY) {
            $this->updateCurriApproveSubstatus($order);
        }
        if ($orderDelivery->type === OrderDelivery::TYPE_PICKUP) {
            $this->updatePickupApproveSubstatus($order);
        }
    }

    private function updatePickupApproveSubstatus(Order $order): void
    {
        $orderDelivery = $order->orderDelivery;
        $now           = Carbon::now();

        if ($orderDelivery->isNeededNow() || $now->diffInMinutes($order->orderDelivery->startTime(),
                false) <= self::LIMIT_MINUTES) {
            $substatusId = Substatus::STATUS_APPROVED_READY_FOR_DELIVERY;
        } else {
            $delayTime = App::make(CalculateJobExecutionTime::class, ['order' => $order])->execute();
            DelayApprovedReadyForDelivery::dispatch($order)->delay($delayTime);
            $substatusId = Substatus::STATUS_APPROVED_AWAITING_DELIVERY;
        }
        App::make(ChangeStatus::class, [
            'order'       => $order,
            'substatusId' => $substatusId,
        ])->execute();
    }

    private function updateCurriApproveSubstatus(Order $order): void
    {
        $orderDelivery = $order->orderDelivery;
        $now           = Carbon::now();

        if ($orderDelivery->isNeededNow() || $now->diffInMinutes($orderDelivery->startTime(),
                false) <= self::LIMIT_MINUTES) {
            App::make(Book::class, ['order' => $order])->execute();
            App::make(ChangeStatus::class, [
                'order'       => $order,
                'substatusId' => Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
            ])->execute();
            Booked::dispatch($order);
        } else {
            $delayTime = App::make(CalculateJobExecutionTime::class, ['order' => $order])->execute();
            DelayBooking::dispatch($order)->delay($delayTime);

            App::make(ChangeStatus::class, [
                'order'       => $order,
                'substatusId' => Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
            ])->execute();
        }
    }
}
