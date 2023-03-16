<?php

namespace App\Handlers\OrderSubstatus;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Models\Order;
use Carbon\Carbon;

abstract class BaseOrderSubstatusHandler implements OrderSubstatusUpdated
{
    const LIMIT_MINUTES = 30;

    public function changeSubstatus(Order $order, int $substatusId)
    {
        return App::make(ChangeStatus::class, [
            'order'       => $order,
            'substatusId' => $substatusId,
        ])->execute();
    }

    protected function diffMinutes($orderDelivery): int
    {
        $now = Carbon::now();

        return $now->diffInMinutes($orderDelivery->startTime(), false);
    }
}
