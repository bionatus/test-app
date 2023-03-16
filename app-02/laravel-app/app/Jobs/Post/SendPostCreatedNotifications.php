<?php

namespace App\Jobs\Post;

use App;
use App\Models\Post;
use App\Models\Scopes\ExceptKey;
use App\Models\User;
use App\Models\User\Scopes\ByFollowedTags;
use App\Notifications\PostCreatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPostCreatedNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
        $this->onConnection('database');
    }

    public function handle()
    {
        $tags  = $this->post->tags()->with('taggable')->get();
        $users = User::with('settingUsers')
            ->scoped(new ByFollowedTags($tags))
            ->scoped(new ExceptKey($this->post->user->getKey()));

        $users->cursor()->each(function(User $user) {
            $user->notify(new PostCreatedNotification($this->post));
        });
    }
}
