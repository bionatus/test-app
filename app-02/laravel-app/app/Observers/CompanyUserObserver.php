<?php

namespace App\Observers;

use App\Models\CompanyUser;

class CompanyUserObserver
{
    public function saved(CompanyUser $companyUser)
    {
        $companyUser->user->verify()->save();
    }

    public function deleted(CompanyUser $companyUser)
    {
        $companyUser->user->verify()->save();
    }
}
