<?php

namespace App\Policies\Nova;

use App\Models\Supply;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplyPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Supply $supply)
    {
        return true;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Supply $supply)
    {
        return true;
    }

    public function delete(User $user, Supply $supply)
    {
        return ($supply->item->orders()->count() == 0);
    }
}
