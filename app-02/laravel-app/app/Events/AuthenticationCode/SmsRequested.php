<?php

namespace App\Events\AuthenticationCode;

use App\Models\AuthenticationCode;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SmsRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private AuthenticationCode $authenticationCode;

    public function __construct(AuthenticationCode $authenticationCode)
    {
        $this->authenticationCode = $authenticationCode;
    }

    public function authenticationCode(): AuthenticationCode
    {
        return $this->authenticationCode;
    }
}
