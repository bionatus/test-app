<?php

namespace App\Listeners\Order;

use App\Events\Order\OrderEvent;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\OrderInvoice\Scopes\ByCreatedMonth;
use App\Models\OrderInvoice\Scopes\ByOrderSupplier;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateOrderInvoice implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderEvent $event)
    {
        $order = $event->order();

        OrderInvoice::create([
            'order_id'      => $order->getKey(),
            'number'        => $this->getNumber($order),
            'type'          => OrderInvoice::TYPE_INVOICE,
            'subtotal'      => $order->subTotalWithDeliveryAndDiscount(),
            'take_rate'     => $order->supplier->take_rate,
            'bid_number'    => $order->bid_number,
            'order_name'    => $order->name,
            'payment_terms' => $order->supplier->terms,
        ]);
    }

    private function getNumber(Order $order): int
    {
        $invoiceNumber = OrderInvoice::scoped(new ByOrderSupplier($order))
            ->scoped(new ByCreatedMonth(Carbon::now()->month))
            ->first();

        if (!$invoiceNumber) {
            return (int) OrderInvoice::max('number') + 1;
        }

        return $invoiceNumber->number;
    }
}
