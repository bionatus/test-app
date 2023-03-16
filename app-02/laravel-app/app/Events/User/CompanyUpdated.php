<?php

namespace App\Events\User;

use App\Models\CompanyUser;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompanyUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private CompanyUser $companyUser;

    public function __construct(CompanyUser $companyUser)
    {
        $this->companyUser = $companyUser;
    }

    public function companyUser(): CompanyUser
    {
        return $this->companyUser;
    }
}
