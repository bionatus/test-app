<?php

namespace App\Jobs\Order;

use App;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\Order\Scopes\ByOrderDeliveryType;
use App\Models\OrderDelivery;
use App\Models\Substatus;
use App\Models\User;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetTotalOrdersInformationNewStatuses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $user;

    public function __construct(User $user)
    {
        $this->onConnection('database');

        $this->user = $user;
    }

    public function handle()
    {
        $user          = $this->user;
        $database      = App::make('firebase.database');
        $databaseNode  = Config::get('mobile.firebase.order_status_node');
        $key           = $databaseNode . $user->getKey();
        $pickUpCounter = $user->orders()
            ->scoped(new ByOrderDeliveryType(OrderDelivery::TYPE_PICKUP))
            ->scoped(new ByLastSubstatuses([
                Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
                Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
            ]))
            ->count();
        $curriCounter  = $user->orders()
            ->scoped(new ByOrderDeliveryType(OrderDelivery::TYPE_CURRI_DELIVERY))
            ->scoped(new ByLastSubstatuses([
                Substatus::STATUS_APPROVED_DELIVERED,
            ]))
            ->count();

        $value = [
            'total_active_orders' => $user->orders()
                ->scoped(new ByLastSubstatuses(array_merge(Substatus::STATUSES_PENDING,
                    Substatus::STATUSES_PENDING_APPROVAL, Substatus::STATUSES_APPROVED)))
                ->count(),
            'total_high_priority' => $user->orders()
                ->scoped(new ByLastSubstatuses([
                    Substatus::STATUS_PENDING_APPROVAL_FULFILLED,
                    Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED,
                ]))
                ->count(),
            'total_low_priority'  => $pickUpCounter + $curriCounter,
        ];
        $database->getReference()->update([$key => $value]);
    }
}
