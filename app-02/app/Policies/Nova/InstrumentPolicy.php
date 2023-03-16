<?php

namespace App\Policies\Nova;

use App\Models\Instrument;
use App\Models\SupportCallCategory;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstrumentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Instrument $instrument)
    {
        return true;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Instrument $instrument)
    {
        return true;
    }

    public function delete(User $user, Instrument $instrument)
    {
        return $instrument->supportCallCategories()->doesntExist();
    }

    public function attachAnySupportCallCategory(User $user, Instrument $instrument)
    {
        return false;
    }

    public function detachSupportCallCategory(User $user, Instrument $instrument, SupportCallCategory $supportCallCategory)
    {
        return false;
    }
}
