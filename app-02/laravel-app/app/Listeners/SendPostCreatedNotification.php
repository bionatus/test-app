<?php

namespace App\Listeners;

use App;
use App\Events\Post\Created;
use App\Jobs\Post\SendPostCreatedNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPostCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Created $event)
    {
        SendPostCreatedNotifications::dispatch($event->post);
    }
}
