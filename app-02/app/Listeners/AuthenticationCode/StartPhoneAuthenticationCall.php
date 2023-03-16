<?php

namespace App\Listeners\AuthenticationCode;

use App\Events\AuthenticationCode\CallRequested;
use App\Jobs\PhoneAuthenticationCall;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StartPhoneAuthenticationCall implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CallRequested $event)
    {
        PhoneAuthenticationCall::dispatch($event->phone());
    }
}
