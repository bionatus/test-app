<?php

namespace App\Observers;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Constants\RequestKeys;
use App\Events\Order\Delivery\Curri\ArrivedAtDestination;
use App\Events\Order\Delivery\Curri\OnRoute;
use App\Models\CurriDelivery;
use App\Models\Substatus;

class CurriDeliveryObserver
{
    public function updated(CurriDelivery $curriDelivery)
    {
        if ($curriDelivery->isDirty(RequestKeys::STATUS)) {
            $onRouteStatuses = CurriDelivery::DELIVERY_STATUSES_ON_ROUTE;
            $originalStatus  = $curriDelivery->getOriginal(RequestKeys::STATUS);
            $currentStatus   = $curriDelivery->status;

            if (!in_array($originalStatus, $onRouteStatuses) && in_array($currentStatus, $onRouteStatuses)) {
                OnRoute::dispatch($curriDelivery);
            }

            $finishedStatuses = CurriDelivery::DELIVERY_FINISHED_STATUSES;
            if (!in_array($originalStatus, $finishedStatuses) && in_array($currentStatus, $finishedStatuses)) {
                $order = App::make(ChangeStatus::class, [
                    'order'       => $curriDelivery->orderDelivery->order,
                    'substatusId' => Substatus::STATUS_APPROVED_DELIVERED,
                ])->execute();
                ArrivedAtDestination::dispatch($order);
            }
        }
    }
}
