<?php

namespace App\Observers;

use App\Actions\Models\Activity\BuildResource;
use App\Http\Resources\Api\V3\Activity\OrderResource;
use App\Jobs\LogActivity;
use App\Jobs\Order\SetTotalOrdersInformationNewStatuses as SetTotalOrdersInformationNewStatusesJob;
use App\Jobs\Supplier\SetPriceAndAvailability;
use App\Jobs\Supplier\SetTotalActiveOrders;
use App\Jobs\Supplier\SetWillCall;
use App\Models\Activity;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use Auth;

class OrderSubstatusObserver
{
    public function created(OrderSubstatus $orderSubstatus): void
    {
        $order    = $orderSubstatus->order;
        $supplier = $order->supplier;

        if ($orderSubstatus->substatus_id === Substatus::STATUS_PENDING_REQUESTED) {
            SetPriceAndAvailability::dispatch($supplier);
        }

        if ($orderSubstatus->isWillCall() && !$this->previousSubstatusIsWillCall($order)) {
            SetWillCall::dispatch($supplier);
        }

        Activity::inLog(Activity::TYPE_ORDER)->forSubject($order)->delete();

        $property = (new BuildResource($order, OrderResource::class))->execute();
        LogActivity::dispatch(Activity::ACTION_UPDATED, Activity::RESOURCE_ORDER, $order, Auth::getUser(), $property,
            Activity::TYPE_ORDER);

        if ($user = $order->user) {
            SetTotalOrdersInformationNewStatusesJob::dispatch($user);
        }

        SetTotalActiveOrders::dispatch($order->supplier);
    }

    private function previousSubstatusIsWillCall(Order $order): bool
    {
        /** @var OrderSubstatus $previousOrderSubstatus */
        $previousOrderSubstatus = $order->orderSubstatuses()->latest(OrderSubstatus::keyName())->skip(1)->first();

        return $previousOrderSubstatus && $previousOrderSubstatus->isWillCall();
    }
}
