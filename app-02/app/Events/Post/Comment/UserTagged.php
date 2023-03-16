<?php

namespace App\Events\Post\Comment;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTagged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Comment $comment;
    public User    $userTagged;

    public function __construct(Comment $comment, User $userTagged)
    {
        $this->comment    = $comment;
        $this->userTagged = $userTagged;
    }
}
