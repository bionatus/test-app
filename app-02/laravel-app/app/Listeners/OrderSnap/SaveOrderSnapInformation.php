<?php

namespace App\Listeners\OrderSnap;

use App\Events\Order\OrderEventInterface;
use App\Jobs\OrderSnap\SaveOrderSnapInformation as SaveOrderSnapInformationJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SaveOrderSnapInformation implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderEventInterface $event)
    {
        SaveOrderSnapInformationJob::dispatch($event->order());
    }
}
