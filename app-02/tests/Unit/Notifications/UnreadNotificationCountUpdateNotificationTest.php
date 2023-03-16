<?php

namespace Tests\Unit\Notifications;

use App\Constants\Notifications;
use App\Notifications\SendsPushNotification;
use App\Notifications\UnreadNotificationCountUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Fcm\FcmMessage;
use ReflectionClass;
use ReflectionException;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class UnreadNotificationCountUpdateNotificationTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(UnreadNotificationCountUpdatedNotification::class, SendsPushNotification::class);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UnreadNotificationCountUpdatedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_has_a_correct_fcm_message_and_payload()
    {
        $unreadNotificationsCount                   = 10;
        $unreadNotificationCountUpdatedNotification = new UnreadNotificationCountUpdatedNotification($unreadNotificationsCount);

        $fcmMessage = $unreadNotificationCountUpdatedNotification->toFcm();

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $data = [
            'type'     => 'resource',
            'resource' => json_encode([
                'notifications_count' => $unreadNotificationsCount,
            ]),
        ];

        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());

        $fcmNotification = $fcmMessage->getNotification();

        $this->assertNull($fcmNotification);
    }

    /** @test */
    public function it_uses_fcm_queue()
    {
        $unreadNotificationsCount                   = 10;
        $unreadNotificationCountUpdatedNotification = new UnreadNotificationCountUpdatedNotification($unreadNotificationsCount);

        $viaQueues = $unreadNotificationCountUpdatedNotification->viaQueues();

        $this->assertEquals([Notifications::VIA_FCM => Notifications::QUEUE_FCM], $viaQueues);
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $unreadNotificationsCount                   = 10;
        $unreadNotificationCountUpdatedNotification = new UnreadNotificationCountUpdatedNotification($unreadNotificationsCount);

        $this->assertSame('database', $unreadNotificationCountUpdatedNotification->connection);
    }
}
