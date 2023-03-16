<?php

namespace Tests\Unit\Observers;

use App\Models\InternalNotification;
use App\Models\User;
use App\Notifications\UnreadNotificationCountUpdatedNotification as UnreadNotificationCount;
use App\Observers\InternalNotificationObserver;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ReflectionProperty;
use Tests\TestCase;

class InternalNotificationObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $internalNotification = InternalNotification::factory()->make(['uuid' => null]);

        $observer = new InternalNotificationObserver();

        $observer->creating($internalNotification);

        $this->assertNotNull($internalNotification->uuid);
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_an_unread_notification_count_update_notification_when_created()
    {
        Notification::fake();

        InternalNotification::flushEventListeners();

        $user = User::factory()->create();
        InternalNotification::factory()->usingUser($user)->count(2)->create();
        $internalNotification = InternalNotification::factory()->usingUser($user)->create();

        $observer = new InternalNotificationObserver();
        $observer->created($internalNotification);

        Notification::assertSentTo([$user], UnreadNotificationCount::class,
            function(UnreadNotificationCount $notification) {
                $property = new ReflectionProperty(UnreadNotificationCount::class, 'unreadNotificationsCount');
                $property->setAccessible(true);
                $this->assertEquals(3, $property->getValue($notification));

                return true;
            });
    }
}
