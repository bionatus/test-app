<?php

namespace App\Listeners\Service;

use App\Events\Service\Log;
use App\Jobs\Service\CreateLog as CreateLogJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateLog implements ShouldQueue
{
    use Queueable;

    public function handle(Log $event)
    {
        CreateLogJob::dispatch($event->serviceName(), $event->request(), $event->response(), $event->model());
    }
}
