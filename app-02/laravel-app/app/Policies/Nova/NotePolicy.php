<?php

namespace App\Policies\Nova;

use App\Models\Note;
use App\Models\NoteCategory;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Note $note)
    {
        return true;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Note $note)
    {
        return true;
    }

    public function delete(User $user, Note $note)
    {
        if ($note->noteCategory->getRouteKey() === NoteCategory::SLUG_GAMIFICATION) {
            return false;
        }

        return true;
    }
}
