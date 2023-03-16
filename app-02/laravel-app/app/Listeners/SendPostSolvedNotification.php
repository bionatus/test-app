<?php

namespace App\Listeners;

use App;
use App\Events\Post\Solution\Created;
use App\Jobs\Comment\SendPostSolvedNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPostSolvedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Created $event)
    {
        SendPostSolvedNotifications::dispatch($event->solution);
    }
}
