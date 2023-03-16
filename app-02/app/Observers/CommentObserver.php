<?php

namespace App\Observers;

use App\Models\Comment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CommentObserver
{
    public function creating(Comment $comment): void
    {
        $comment->uuid = Str::uuid();
    }

    public function updating(Comment $comment): void
    {
        if ($comment->isDirty('message')) {
            $comment->content_updated_at = Carbon::now();
        }
    }
}
