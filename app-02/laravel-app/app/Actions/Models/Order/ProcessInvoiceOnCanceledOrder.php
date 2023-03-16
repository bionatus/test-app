<?php

namespace App\Actions\Models\Order;

use App;
use App\Models\Order;
use App\Models\OrderInvoice;

class ProcessInvoiceOnCanceledOrder
{
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function execute(): void
    {
        $invoice = $this->order->invoice;

        if (!$this->order->isCanceled() || !$invoice || $this->order->credit()->exists()) {
            return;
        }

        if ($invoice->processed_at) {
            $invoice->replicate()->fill([
                'type'         => OrderInvoice::TYPE_CREDIT,
                'processed_at' => null,
            ])->save();
        } else {
            $invoice->delete();
        }
    }
}
