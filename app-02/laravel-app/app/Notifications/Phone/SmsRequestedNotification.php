<?php

namespace App\Notifications\Phone;

use App\Channels\SmsChannel;
use App\Models\AuthenticationCode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SmsRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private AuthenticationCode $authenticationCode;

    public function __construct(AuthenticationCode $authenticationCode)
    {
        $this->onConnection('sync');
        $this->authenticationCode = $authenticationCode;
    }

    public function via()
    {
        return [SmsChannel::class];
    }

    public function toSms(): string
    {
        $code = $this->authenticationCode->code;
        $type = $this->authenticationCode->isLogin() ? 'login' : 'verification';

        return "Your Bluon {$type} code is: {$code}";
    }
}
