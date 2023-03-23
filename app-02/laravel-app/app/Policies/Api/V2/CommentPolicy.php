<?php

namespace App\Policies\Api\V2;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Comment $comment)
    {
        if ($comment->isSolution()) {
            return false;
        }

        return $user->isModerator() || $comment->isOwner($user);
    }

    public function delete(User $user, Comment $comment)
    {
        if ($comment->isSolution()) {
            return $user->isModerator();
        }

        return $user->isModerator() || $comment->isOwner($user);
    }
}
