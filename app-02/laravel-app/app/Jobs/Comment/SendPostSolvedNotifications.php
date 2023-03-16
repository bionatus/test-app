<?php

namespace App\Jobs\Comment;

use App;
use App\Models\Comment;
use App\Models\Scopes\ExceptKey;
use App\Models\User;
use App\Models\User\Scopes\ByFollowedTags;
use App\Notifications\PostSolvedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPostSolvedNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Comment $solution;

    public function __construct(Comment $solution)
    {
        $this->solution = $solution;
        $this->onConnection('database');
    }

    public function handle()
    {
        $post  = $this->solution->post;
        $tags  = $post->tags()->with('taggable')->get();
        $users = User::with('settingUsers')
            ->scoped(new ByFollowedTags($tags))
            ->scoped(new ExceptKey($post->user->getKey()));

        $users->cursor()->each(function(User $user) use ($post) {
            $user->notify(new PostSolvedNotification($post));
        });
    }
}
