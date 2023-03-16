<?php

namespace App\Console\Commands;

use App;
use App\Actions\Models\Order\AddPoints as AddPointsAction;
use App\Models\Order;
use App\Models\Order\Scopes\ByActionWithoutPoints;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\Order\Scopes\ByUserNotNull;
use App\Models\Point;
use App\Models\Substatus;
use Illuminate\Console\Command;
use Throwable;

class AddPointsUnprocessedOrdersCommand extends Command
{
    protected $signature   = 'add-points:unprocessed-orders';
    protected $description = 'Add points to unprocessed orders';

    public function handle()
    {
        $action  = Point::ACTION_ORDER_APPROVED;
        $builder = Order::with('user')
            ->scoped(new ByLastSubstatuses(array_merge(Substatus::STATUSES_APPROVED, Substatus::STATUSES_COMPLETED)))
            ->scoped(new ByUserNotNull())
            ->scoped(new ByActionWithoutPoints($action));

        $builder->cursor()->each(/* @throws Throwable */ function(Order $order) use ($action) {
            App::make(AddPointsAction::class, ['order' => $order, 'action' => $action])->execute();
        });
    }
}

