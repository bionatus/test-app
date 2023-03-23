<?php

namespace App\Policies\Nova;

use App\Models\SupplyCategory;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplyCategoryPolicy
{
    use HandlesAuthorization;

    public function view(User $user, SupplyCategory $supplyCategory)
    {
        return true;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, SupplyCategory $supplyCategory)
    {
        return true;
    }

    public function delete(User $user, SupplyCategory $supplyCategory)
    {
        return ($supplyCategory->children()->count() == 0) && ($supplyCategory->supplies()->count() == 0);
    }

    public function addSupply(User $user, SupplyCategory $supplyCategory)
    {
        return $supplyCategory->children()->count() == 0;
    }
}
