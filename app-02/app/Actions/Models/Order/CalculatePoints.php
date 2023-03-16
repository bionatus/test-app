<?php

namespace App\Actions\Models\Order;

use App;
use App\Models\AppSetting;
use App\Models\Order;
use App\Models\Scopes\ByRouteKey;
use App\Types\Point;

class CalculatePoints
{
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function execute(): Point
    {
        $coefficient = $this->order->user->currentLevel()->coefficient;
        /** @var AppSetting $multiplierSetting */
        $multiplierSetting = AppSetting::scoped(new ByRouteKey(AppSetting::SLUG_BLUON_POINTS_MULTIPLIER))->first();
        $multiplier        = (int) $multiplierSetting->value;
        $amount            = $this->order->isCompleted() ? $this->order->total : $this->order->subTotalWithDelivery();
        $points            = (int) ceil($amount * $coefficient * $multiplier);

        return new Point($points, $coefficient, $multiplier);
    }
}
