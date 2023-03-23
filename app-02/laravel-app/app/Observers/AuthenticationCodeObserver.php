<?php

namespace App\Observers;

use App\Models\AuthenticationCode;

class AuthenticationCodeObserver
{
    use CanGenerateNumber;

    public function creating(AuthenticationCode $authenticationCode): void
    {
        $authenticationCode->code ??= $this->generateStringNumber();
    }
}
