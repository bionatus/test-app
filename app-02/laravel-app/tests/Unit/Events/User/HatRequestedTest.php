<?php

namespace Tests\Unit\Events\User;

use App\Events\User\HatRequested;
use App\Listeners\SendHatRequestedEmail;
use App\Models\User;
use Tests\TestCase;

class HatRequestedTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(HatRequested::class, [
            SendHatRequestedEmail::class,
        ]);
    }

    /** @test */
    public function it_returns_its_user()
    {
        $user = new User();

        $event = new HatRequested($user);

        $this->assertSame($user, $event->user());
    }
}
