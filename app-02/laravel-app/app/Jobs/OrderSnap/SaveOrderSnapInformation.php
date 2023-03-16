<?php

namespace App\Jobs\OrderSnap;

use App;
use App\Models\ItemOrder;
use App\Models\ItemOrderSnap;
use App\Models\Order;
use App\Models\OrderSnap;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveOrderSnapInformation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Order $order;

    public function __construct(Order $order)
    {
        $this->onConnection('database');

        $this->order = $order;
    }

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        $order = $this->order;

        DB::transaction(function() use ($order) {
            $orderSnap = OrderSnap::create([
                'order_id'      => $order->getKey(),
                'user_id'       => $order->user_id,
                'supplier_id'   => $order->supplier_id,
                'oem_id'        => $order->oem_id,
                'working_on_it' => $order->working_on_it,
                'status'        => $order->getStatusName(),
                'bid_number'    => $order->bid_number,
                'discount'      => $order->discount,
                'tax'           => $order->tax,
            ]);

            $order->itemOrders->each(function(ItemOrder $itemOrder) use ($orderSnap) {
                ItemOrderSnap::create([
                    'order_snap_id'            => $orderSnap->getKey(),
                    'item_id'                  => $itemOrder->item_id,
                    'order_id'                 => $itemOrder->order_id,
                    'replacement_id'           => $itemOrder->replacement_id,
                    'quantity'                 => $itemOrder->quantity,
                    'price'                    => $itemOrder->price,
                    'supply_detail'            => $itemOrder->supply_detail,
                    'custom_detail'            => $itemOrder->custom_detail,
                    'generic_part_description' => $itemOrder->generic_part_description,
                    'status'                   => $itemOrder->status,
                ]);
            });
        });
    }
}
