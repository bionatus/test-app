<?php

namespace Tests\Unit\Notifications\User;

use App\Models\InternalNotification;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\User;
use App\Notifications\SendsPushNotification;
use App\Notifications\User\OrderApprovedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

class OrderApprovedNotificationTest extends TestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(OrderApprovedNotification::class, SendsPushNotification::class, ['via']);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(OrderApprovedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $notification = new OrderApprovedNotification(new Order(), new InternalNotification());

        $this->assertEquals('database', $notification->connection);
    }

    /** @test
     * @dataProvider pushNotifications
     */
    public function it_can_notify_via_fcm(bool $expected)
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('shouldSendInAppNotification')->withAnyArgs()->once()->andReturn($expected);
        $user->shouldReceive('shouldSendSmsNotification')->withAnyArgs()->once();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->withArgs(['user'])->once()->andReturn($user);

        $notification = new OrderApprovedNotification($order, new InternalNotification());

        $this->assertSame($expected, in_array(FcmChannel::class, $notification->via(null)));
    }

    /** @test
     * @dataProvider pushNotifications
     */
    public function it_can_notify_via_sms(bool $expected)
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('shouldSendInAppNotification')->withAnyArgs()->once();
        $user->shouldReceive('shouldSendSmsNotification')->withAnyArgs()->once()->andReturn($expected);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->withArgs(['user'])->once()->andReturn($user);

        $notification = new OrderApprovedNotification($order, new InternalNotification());

        $this->assertSame($expected, in_array(TwilioChannel::class, $notification->via(null)));
    }

    public function pushNotifications(): array
    {
        return [[true], [false]];
    }

    /** @test
     * @dataProvider orderDataProvider
     */
    public function it_sets_twilio_message(bool $hasDelivery, string $deliveryMethod)
    {
        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('isPickup')->withNoArgs()->once()->andReturn(!$hasDelivery);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);

        $toTwilio = (new OrderApprovedNotification($order, new InternalNotification()))->toTwilio(null);

        $smsText  = "Bluon - Your approved order has been received and is being worked on for $deliveryMethod. Do Not Reply to this text.";
        $expected = (new TwilioSmsMessage())->content($smsText);

        $this->assertInstanceOf(TwilioSmsMessage::class, $toTwilio);
        $this->assertEquals($expected, $toTwilio);
    }

    /** @test
     * @dataProvider orderDataProvider
     */
    public function it_has_a_correct_fcm_message_and_payload(bool $hasDelivery, string $deliveryMethod)
    {
        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('isPickup')->withNoArgs()->once()->andReturn(!$hasDelivery);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($orderRouteKey = 'route key');
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);

        $internalNotification = Mockery::mock(InternalNotification::class);
        $internalNotification->shouldReceive('getRouteKey')
            ->withNoArgs()
            ->once()
            ->andReturn($internalNotificationRouteKey = 'route key');

        $toFcm        = (new OrderApprovedNotification($order, $internalNotification))->toFcm();
        $notification = $toFcm->getNotification();

        $body  = "Your approved order has been received and is being worked on for $deliveryMethod.";
        $data  = [
            'source' => json_encode([
                'event'                    => 'approved',
                'type'                     => 'order',
                'id'                       => $orderRouteKey,
                'internal_notification_id' => $internalNotificationRouteKey,
            ]),
        ];
        $title = 'Order Approved';

        $this->assertInstanceOf(FcmMessage::class, $toFcm);
        $this->assertArrayHasKeysAndValues($data, $toFcm->getData());
        $this->assertEquals($body, $notification->getBody());
        $this->assertEquals($title, $notification->getTitle());
    }

    public function orderDataProvider(): array
    {
        return [
            // hasDelivery, deliveryMethod
            [false, 'pickup'],
            [true, 'delivery'],
        ];
    }
}
