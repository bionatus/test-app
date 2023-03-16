<?php

namespace App\Policies\LiveApi\V1;

use App\Models\Staff;
use Illuminate\Auth\Access\HandlesAuthorization;

class StaffPolicy
{
    use HandlesAuthorization;

    public function setInitialPassword(Staff $staff): bool
    {
        return !$staff->hasSetInitialPassword();
    }
}
