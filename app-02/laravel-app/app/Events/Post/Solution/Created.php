<?php

namespace App\Events\Post\Solution;

use App\Models\Comment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Created
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Comment $solution;

    public function __construct(Comment $comment)
    {
        $this->solution = $comment;
    }
}
