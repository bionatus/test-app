<?php

namespace App\Policies\Nova;

use App\Models\SupportCallCategory;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupportCallCategoryPolicy
{
    use HandlesAuthorization;

    public function view(User $user, SupportCallCategory $supportCallCategory)
    {
        return true;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, SupportCallCategory $supportCallCategory)
    {
        return true;
    }

    public function delete(User $user, SupportCallCategory $supportCallCategory)
    {
        return $supportCallCategory->children()->doesntExist();
    }
}
