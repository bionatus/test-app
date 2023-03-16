<?php

namespace App\Policies\LiveApi\V1;

use App\Models\Scopes\ByUser;
use App\Models\Staff;
use App\Models\SupplierUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function confirm(Staff $staff, User $user): bool
    {
        return $this->canUpdate($staff, $user, SupplierUser::STATUS_UNCONFIRMED);
    }

    public function delete(Staff $staff, User $user): bool
    {
        return $this->canUpdate($staff, $user, SupplierUser::STATUS_CONFIRMED);
    }

    public function remove(Staff $staff, User $user): bool
    {
        return $this->canUpdate($staff, $user, SupplierUser::STATUS_UNCONFIRMED);
    }

    public function restore(Staff $staff, User $user): bool
    {
        return $this->canUpdate($staff, $user, SupplierUser::STATUS_REMOVED);
    }

    public function update(Staff $staff, User $user): bool
    {
        return $this->canUpdate($staff, $user, SupplierUser::STATUS_CONFIRMED);
    }

    public function updateUnconfirmed(Staff $staff, User $user): bool
    {
        return $this->canUpdate($staff, $user, SupplierUser::STATUS_UNCONFIRMED);
    }

    private function canUpdate(Staff $staff, User $user, string $status): bool
    {
        $supplier = $staff->supplier;

        /** @var SupplierUser $supplierUser */
        $supplierUser = $supplier->supplierUsers()->scoped(new ByUser($user))->first();

        if (!$supplierUser) {
            return false;
        }

        if ($supplierUser->status !== $status) {
            return false;
        }

        return true;
    }
}
