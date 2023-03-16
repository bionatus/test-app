<?php

namespace App\Console\Commands\Invoices;

use App;
use App\Actions\Models\Order\ProcessInvoiceOnCanceledOrder;
use App\Models\Order;
use App\Models\Order\Scopes\ByLastSubstatuses;
use App\Models\Substatus;
use Illuminate\Console\Command;

class CreateCreditForCanceledOrdersWithProcessedInvoice extends Command
{
    protected $signature   = 'invoices:create-credit-for-canceled-orders';
    protected $description = 'Create a credit for canceled orders with processed invoice and delete unprocessed invoices';

    public function handle()
    {
        $orders = Order::scoped(new ByLastSubstatuses(Substatus::STATUSES_CANCELED))
            ->whereHas('invoice')
            ->whereDoesntHave('credit')
            ->cursor();

        $orders->each(function(Order $order) {
            App::make(ProcessInvoiceOnCanceledOrder::class, ['order' => $order])->execute();
        });
    }
}
