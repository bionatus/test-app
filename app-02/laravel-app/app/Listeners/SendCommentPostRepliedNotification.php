<?php

namespace App\Listeners;

use App\Events\Post\Comment\Created;
use App\Models\Scopes\ExceptKeys;
use App\Models\User;
use App\Models\User\Scopes\ByCommentPost;
use App\Notifications\CommentPostRepliedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCommentPostRepliedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Created $event)
    {
        $comment = $event->comment;
        $post    = $comment->post;
        $users   = User::scoped(new ByCommentPost($post))->scoped(new ExceptKeys([
            $comment->user_id,
            $post->user_id,
        ]))->get();

        $users->each(function(User $user) use ($comment) {
            $user->notify(new CommentPostRepliedNotification($comment));
        });
    }
}
