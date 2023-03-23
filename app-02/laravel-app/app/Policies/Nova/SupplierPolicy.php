<?php

namespace App\Policies\Nova;

use App\Models\Supplier;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Supplier $supplier)
    {
        return true;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Supplier $supplier)
    {
        return true;
    }

    public function delete(User $user, Supplier $supplier)
    {
        return false;
    }

    public function addStaff(User $user, Supplier $supplier)
    {
        return $supplier->staff()->count() < 10;
    }
}
