<?php

namespace Tests\Unit\Events\User;

use App\Events\User\HubspotFieldUpdated;
use App\Listeners\User\UpdateHubspotContact;
use App\Models\User;
use Tests\TestCase;

class HubspotFieldUpdatedTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(HubspotFieldUpdated::class, [
            UpdateHubspotContact::class,
        ]);
    }

    /** @test */
    public function it_returns_its_user()
    {
        $user = new User();

        $event = new HubspotFieldUpdated($user);

        $this->assertSame($user, $event->user());
    }
}
