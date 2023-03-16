<?php

namespace Tests\Unit\Events\User;

use App\Events\User\SuppliersUpdated;
use App\Listeners\User\CallUserVerificationProcess;
use App\Listeners\User\UpdateHubspotStores;
use App\Models\User;
use Tests\TestCase;

class SuppliersUpdatedTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(SuppliersUpdated::class, [
            UpdateHubspotStores::class,
            CallUserVerificationProcess::class,
        ]);
    }

    /** @test */
    public function it_returns_its_user()
    {
        $user = new User();

        $event = new SuppliersUpdated($user);

        $this->assertSame($user, $event->user());
    }
}
