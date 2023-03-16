<?php

namespace App\Policies\Nova;

use App\Models\AppVersion;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppVersionPolicy
{
    use HandlesAuthorization;

    public function view(User $user, AppVersion $version)
    {
        return true;
    }

    public function create(User $user)
    {
        return false;
    }

    public function update(User $user, AppVersion $version)
    {
        return true;
    }

    public function delete(User $user, AppVersion $version)
    {
        return false;
    }
}
