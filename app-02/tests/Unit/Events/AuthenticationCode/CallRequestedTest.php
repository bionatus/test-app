<?php

namespace Tests\Unit\Events\AuthenticationCode;

use App\Events\AuthenticationCode\CallRequested;
use App\Listeners\AuthenticationCode\StartPhoneAuthenticationCall;
use App\Models\Phone;
use Tests\TestCase;

class CallRequestedTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(CallRequested::class, [
            StartPhoneAuthenticationCall::class,
        ]);
    }

    /** @test */
    public function it_returns_its_phone()
    {
        $phone = new Phone();

        $event = new CallRequested($phone);

        $this->assertSame($phone, $event->phone());
    }
}
