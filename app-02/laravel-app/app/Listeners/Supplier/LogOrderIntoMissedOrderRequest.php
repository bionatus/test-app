<?php

namespace App\Listeners\Supplier;

use App\Events\Order\OrderEventInterface;
use App\Models\Scopes\Except;
use App\Models\SupplierUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogOrderIntoMissedOrderRequest implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onConnection('database');
    }

    public function handle(OrderEventInterface $event)
    {
        $order           = $event->order();
        $missedSuppliers = $order->user->visibleSupplierUsers()
            ->scoped(new Except('supplier_id', $order->supplier_id))
            ->get();

        $data = $missedSuppliers->map(function(SupplierUser $supplierUser) use ($order) {
            return [
                'order_id'           => $order->getKey(),
                'missed_supplier_id' => $supplierUser->supplier_id,
                'created_at'         => $order->created_at,
            ];
        });

        $order->missedOrderRequests()->insert($data->toArray());
    }
}
