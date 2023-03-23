<?php

namespace App\Policies\Nova;

use App\Models\Scopes\ByUser;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User as UserModel;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user)
    {
        return true;
    }

    public function create(User $user)
    {
        return true;
    }

    public function delete(User $user)
    {
        return true;
    }

    public function update(User $user)
    {
        return true;
    }

    public function viewSupplier(User $user, UserModel $userModel, Supplier $supplier)
    {
        return false;
    }

    public function detachSupplier(User $user, UserModel $userModel, Supplier $supplier)
    {
        /** @var SupplierUser $supplierUser */
        $supplierUser = $supplier->supplierUsers()->where('user_id', $userModel->getKey())->first();

        return $supplierUser->visible_by_user && !$supplierUser->customer_tier && !$supplier->orders()
                ->scoped(new ByUser($userModel))
                ->count();
    }
}
