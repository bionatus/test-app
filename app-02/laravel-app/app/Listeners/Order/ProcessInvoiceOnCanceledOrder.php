<?php

namespace App\Listeners\Order;

use App;
use App\Actions\Models\Order\ProcessInvoiceOnCanceledOrder as ProcessInvoiceOnCanceledOrderAction;
use App\Events\Order\Canceled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessInvoiceOnCanceledOrder implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Canceled $event)
    {
        App::make(ProcessInvoiceOnCanceledOrderAction::class, ['order' => $event->order()])->execute();
    }
}
