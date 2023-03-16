<?php

namespace Tests\Unit\Notifications\User;

use App\Models\InternalNotification;
use App\Models\Order;
use App\Models\User;
use App\Notifications\SendsPushNotification;
use App\Notifications\User\CurriDeliveryOnRoutePushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

class CurriDeliveryOnRoutePushNotificationTest extends TestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_traits()
    {
        $this->assertUseTrait(CurriDeliveryOnRoutePushNotification::class, Queueable::class);
        $this->assertUseTrait(CurriDeliveryOnRoutePushNotification::class, SendsPushNotification::class, ['via']);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(CurriDeliveryOnRoutePushNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $notification = new CurriDeliveryOnRoutePushNotification(new Order());

        $this->assertEquals('database', $notification->connection);
    }

    /** @test
     * @dataProvider viaProvider
     */
    public function it_is_sent_via_fcm_if_requirements_are_met(bool $expected)
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('disabled_at')->once()->andReturnTrue();
        $user->shouldReceive('shouldSendPushNotificationWithoutSetting')->withNoArgs()->once()->andReturn($expected);

        $notification = new CurriDeliveryOnRoutePushNotification(new Order());

        $this->assertSame($expected, in_array(FcmChannel::class, $notification->via($user)));
    }

    public function viaProvider(): array
    {
        return [[true], [false]];
    }

    /** @test
     * @dataProvider internalNotificationDataProvider
     */
    public function it_creates_an_internal_notification_if_requirements_are_met($enabled)
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->times((int) $enabled)->andReturn($orderId = 'order id');

        $internalNotifications = Mockery::mock(HasMany::class);
        $internalNotifications->shouldReceive('create')->with([
            'message'      => 'Your driver is on the way!',
            'source_event' => 'on_route',
            'source_type'  => 'order',
            'source_id'    => $orderId,
        ])->times((int) $enabled)->andReturnNull();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('internalNotifications')
            ->withNoArgs()
            ->times((int) $enabled)
            ->andReturn($internalNotifications);
        $user->shouldReceive('getAttribute')->with('disabled_at')->once()->andReturn(!$enabled);
        $user->shouldReceive('shouldSendPushNotificationWithoutSetting')->withNoArgs()->once()->andReturnFalse();

        (new CurriDeliveryOnRoutePushNotification($order))->via($user);
    }

    public function internalNotificationDataProvider(): array
    {
        return [[true], [false]];
    }

    /** @test */
    public function it_has_a_correct_fcm_message_and_payload()
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->twice()->andReturn($orderId = 'order id');

        $internalNotification = Mockery::mock(InternalNotification::class);
        $internalNotification->shouldReceive('getRouteKey')
            ->withNoArgs()
            ->once()
            ->andReturn($internalNotificationId = 'internal notification id');

        $internalNotifications = Mockery::mock(HasMany::class);
        $internalNotifications->shouldReceive('create')->with([
            'message'      => 'Your driver is on the way!',
            'source_event' => 'on_route',
            'source_type'  => 'order',
            'source_id'    => $orderId,
        ])->once()->andReturn($internalNotification);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('internalNotifications')->withNoArgs()->once()->andReturn($internalNotifications);
        $user->shouldReceive('getAttribute')->with('disabled_at')->once()->andReturnFalse();
        $user->shouldReceive('shouldSendPushNotificationWithoutSetting')->withNoArgs()->once()->andReturnTrue();

        $notification = new CurriDeliveryOnRoutePushNotification($order);
        $notification->via($user);
        $fcmMessage = $notification->toFcm();

        $this->assertInstanceOf(FcmMessage::class, $fcmMessage);

        $data = [
            'source' => json_encode([
                'event'                    => 'on_route',
                'type'                     => 'order',
                'id'                       => $orderId,
                'internal_notification_id' => $internalNotificationId,
            ]),
        ];
        $this->assertArrayHasKeysAndValues($data, $fcmMessage->getData());

        $fcmNotification = $fcmMessage->getNotification();

        $this->assertEquals('Your driver is on the way!', $fcmNotification->getBody());
        $this->assertEquals('New Message', $fcmNotification->getTitle());
    }
}
