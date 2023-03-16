<?php

namespace App\Listeners;

use App\Events\Post\Comment\Created;
use App\Notifications\PostRepliedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPostRepliedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Created $event)
    {
        $comment = $event->comment;
        $post    = $comment->post;
        $user    = $post->user;

        if ($comment->user_id !== $user->id) {
            $user->notify(new PostRepliedNotification($post));
        }
    }
}
