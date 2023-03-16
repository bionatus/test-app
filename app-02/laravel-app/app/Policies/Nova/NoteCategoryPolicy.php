<?php

namespace App\Policies\Nova;

use App\Models\NoteCategory;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NoteCategoryPolicy
{
    use HandlesAuthorization;

    public function view(User $user, NoteCategory $noteCategory)
    {
        return true;
    }

    public function create(User $user)
    {
        return false;
    }

    public function update(User $user, NoteCategory $noteCategory)
    {
        return false;
    }

    public function delete(User $user, NoteCategory $noteCategory)
    {
        return false;
    }
}
