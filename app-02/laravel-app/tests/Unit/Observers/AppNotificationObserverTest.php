<?php

namespace Tests\Unit\Observers;

use App\AppNotification;
use App\Models\User;
use App\Notifications\UnreadNotificationCountUpdatedNotification as UnreadNotificationCount;
use App\Observers\AppNotificationObserver;
use Database\Factories\AppNotificationFactory;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ReflectionProperty;
use Tests\TestCase;

class AppNotificationObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @throws Exception
     */
    public function it_sends_an_unread_notification_count_update_notification_when_created()
    {
        Notification::fake();

        AppNotification::flushEventListeners();
        $user = User::factory()->create();
        (new AppNotificationFactory())->usingUser($user)->count(2)->create();
        /** @var AppNotification $appNotification */
        $appNotification = (new AppNotificationFactory())->usingUser($user)->create();

        $observer = new AppNotificationObserver();
        $observer->created($appNotification);

        Notification::assertSentTo([$user], UnreadNotificationCount::class,
            function(UnreadNotificationCount $notification) {
                $property = new ReflectionProperty(UnreadNotificationCount::class, 'unreadNotificationsCount');
                $property->setAccessible(true);
                $this->assertEquals(3, $property->getValue($notification));

                return true;
            });
    }

    /** @test
     * @throws Exception
     */
    public function it_does_not_send_an_unread_notification_count_update_notification_when_there_is_no_user_associated()
    {
        Notification::fake();

        AppNotification::flushEventListeners();
        /** @var AppNotification $appNotification */
        $appNotification = (new AppNotificationFactory())->create(['user_id' => null]);

        $observer = new AppNotificationObserver();
        $observer->created($appNotification);

        Notification::assertNothingSent();
    }
}
