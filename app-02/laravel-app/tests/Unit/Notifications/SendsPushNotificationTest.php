<?php

namespace Tests\Unit\Notifications;

use App\Constants\Notifications;
use App\Notifications\SendsPushNotification;
use Config;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\Resources\Notification;
use Str;
use Tests\TestCase;

class SendsPushNotificationTest extends TestCase
{
    /** @var SendsPushNotification $class */
    private $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->class = new class {
            use SendsPushNotification;
        };
    }

    /** @test */
    public function it_returns_valid_apns_config()
    {
        $config  = $this->class->apnsConfig();
        $options = $config->getFcmOptions();

        $this->assertEquals('analytics_ios', $options->getAnalyticsLabel());
    }

    /** @test */
    public function it_returns_valid_android_config()
    {
        $config  = $this->class->androidConfig();
        $options = $config->getFcmOptions();

        $this->assertEquals('analytics_android', $options->getAnalyticsLabel());
    }

    /** @test */
    public function it_returns_valid_message()
    {
        $notification = Notification::create();
        $message      = $this->class->message($notification);

        $this->assertEquals($notification, $message->getNotification());
    }

    /** @test
     * @dataProvider pushNotificationsEnabledProvider
     */
    public function it_can_notify_via_fcm(bool $enabled)
    {
        Config::set('notifications.push.enabled', $enabled);

        $this->assertSame($enabled, Str::contains(FcmChannel::class, $this->class->via()));
    }

    public function pushNotificationsEnabledProvider(): array
    {
        return [
            'enabled'  => [true],
            'disabled' => [false],
        ];
    }

    /** @test */
    public function it_uses_fcm_queue()
    {
        $viaQueues = $this->class->viaQueues();

        $this->assertEquals([Notifications::VIA_FCM => Notifications::QUEUE_FCM], $viaQueues);
    }
}
