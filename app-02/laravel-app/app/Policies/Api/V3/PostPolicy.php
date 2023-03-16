<?php

namespace App\Policies\Api\V3;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Post $post)
    {
        if ($post->isSolved()) {
            return false;
        }

        return $user->isModerator() || $post->isOwner($user);
    }

    public function delete(User $user, Post $post)
    {
        if ($post->pinned) {
            return false;
        }

        if ($post->isSolved()) {
            return $user->isModerator();
        }

        return $user->isModerator() || $post->isOwner($user);
    }

    public function solve(User $user, Post $post)
    {
        return $user->isModerator() || $post->isOwner($user);
    }

    public function unSolve(User $user, Post $post)
    {
        return $user->isModerator() || $post->isOwner($user);
    }

    public function pin(User $user, Post $post)
    {
        return $user->isModerator();
    }

    public function unPin(User $user, Post $post)
    {
        return $user->isModerator();
    }
}
