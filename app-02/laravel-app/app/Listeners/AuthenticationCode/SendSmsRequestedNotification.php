<?php

namespace App\Listeners\AuthenticationCode;

use App\Events\AuthenticationCode\SmsRequested;
use App\Notifications\Phone\SmsRequestedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSmsRequestedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(SmsRequested $event)
    {
        $authenticationCode = $event->authenticationCode();

        $phone = $authenticationCode->phone;

        $phone->notify(new SmsRequestedNotification($authenticationCode));
    }
}
