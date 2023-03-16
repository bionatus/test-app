<?php

namespace App\Listeners\Order;

use App;
use App\Actions\Models\Order\AddPoints as AddPointsAction;
use App\Events\Order\OrderEventInterface;
use App\Models\Point;
use App\Models\Point\Scopes\ByAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class AddPoints implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * @throws Throwable
     */
    public function handle(OrderEventInterface $event)
    {
        $order  = $event->order();
        $user   = $order->user;
        $action = $order->isCompleted() ? Point::ACTION_ORDER_COMPLETED : Point::ACTION_ORDER_APPROVED;

        $points = $order->points()->scoped(new ByAction($action))->exists();

        if ($user && !$points) {
            App::make(AddPointsAction::class, ['order' => $order, 'action' => $action])->execute();
        }
    }
}
