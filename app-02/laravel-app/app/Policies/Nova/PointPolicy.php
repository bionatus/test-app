<?php

namespace App\Policies\Nova;

use App\Models\Point;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PointPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Point $point)
    {
        return false;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Point $point)
    {
        return false;
    }

    public function delete(User $user, Point $point)
    {
        return false;
    }
}
