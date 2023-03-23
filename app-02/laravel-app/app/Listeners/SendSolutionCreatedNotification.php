<?php

namespace App\Listeners;

use App\Events\Post\Solution\Created;
use App\Notifications\SolutionCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSolutionCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Created $event)
    {
        $solution = $event->solution;
        $post     = $solution->post;
        $user     = $solution->user;

        if ($user->id !== $post->user_id) {
            $user->notify(new SolutionCreatedNotification($solution));
        }
    }
}
