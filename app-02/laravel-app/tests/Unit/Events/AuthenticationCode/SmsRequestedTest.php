<?php

namespace Tests\Unit\Events\AuthenticationCode;

use App\Events\AuthenticationCode\SmsRequested;
use App\Listeners\AuthenticationCode\SendSmsRequestedNotification;
use App\Models\AuthenticationCode;
use Tests\TestCase;

class SmsRequestedTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(SmsRequested::class, [
            SendSmsRequestedNotification::class,
        ]);
    }

    /** @test */
    public function it_returns_its_authentication_code()
    {
        $authenticationCode = new AuthenticationCode();

        $event = new SmsRequested($authenticationCode);

        $this->assertSame($authenticationCode, $event->authenticationCode());
    }
}
