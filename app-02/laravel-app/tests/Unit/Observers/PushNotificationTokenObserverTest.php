<?php

namespace Tests\Unit\Observers;

use App\Models\PushNotificationToken;
use App\Observers\PushNotificationTokenObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushNotificationTokenObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $pushNotificationToken = PushNotificationToken::factory()->make(['uuid' => null]);

        $observer = new PushNotificationTokenObserver();

        $observer->creating($pushNotificationToken);

        $this->assertNotNull($pushNotificationToken->uuid);
    }
}
