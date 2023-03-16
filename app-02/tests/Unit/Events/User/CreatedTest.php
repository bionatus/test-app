<?php

namespace Tests\Unit\Events\User;

use App\Events\User\Created;
use App\Listeners\User\CreateHubspotContact;
use App\Models\User;
use Tests\TestCase;

class CreatedTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(Created::class, [
            CreateHubspotContact::class,
        ]);
    }

    /** @test */
    public function it_returns_its_user()
    {
        $user = new User();

        $event = new Created($user);

        $this->assertSame($user, $event->user());
    }
}
