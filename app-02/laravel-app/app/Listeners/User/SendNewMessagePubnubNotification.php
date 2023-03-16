<?php

namespace App\Listeners\User;

use App\Events\PubnubChannel\NewMessageEventInterface;
use App\Notifications\User\NewMessagePubnubNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Str;

class SendNewMessagePubnubNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(NewMessageEventInterface $event)
    {
        $supplier = $event->supplier();
        $user     = $event->user();
        $message  = Str::limit($event->message(), 250);

        $user->notify(new NewMessagePubnubNotification($supplier, $user, $message));
    }
}
