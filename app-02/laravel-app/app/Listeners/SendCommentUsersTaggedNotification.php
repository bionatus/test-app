<?php

namespace App\Listeners;

use App\Events\Post\Comment\UserTagged;
use App\Notifications\UserTaggedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCommentUsersTaggedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserTagged $event)
    {
        $comment = $event->comment;
        $user    = $event->userTagged;
        $user->notify(new UserTaggedNotification($comment));
    }
}
