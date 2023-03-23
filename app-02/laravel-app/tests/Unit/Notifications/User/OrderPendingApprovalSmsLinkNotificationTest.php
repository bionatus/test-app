<?php

namespace Tests\Unit\Notifications\User;

use App\Models\Order;
use App\Models\User;
use App\Notifications\User\OrderPendingApprovalSmsLinkNotification;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use ReflectionClass;
use Str;
use Tests\TestCase;

class OrderPendingApprovalSmsLinkNotificationTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(OrderPendingApprovalSmsLinkNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $notification = new OrderPendingApprovalSmsLinkNotification(new Order());

        $this->assertEquals('database', $notification->connection);
    }

    /** @test
     * @dataProvider pushNotifications
     */
    public function it_can_notify_via_sms(bool $expected)
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('shouldSendSmsNotification')->withAnyArgs()->once()->andReturn($expected);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->withArgs(['user'])->once()->andReturn($user);

        $notification = new OrderPendingApprovalSmsLinkNotification($order);

        $this->assertSame($expected, in_array(TwilioChannel::class, $notification->via(null)));
    }

    public function pushNotifications(): array
    {
        return [[true], [false]];
    }

    /** @test */
    public function it_sets_twilio_message()
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($routeKey = 'route key');

        Config::set('live.url', $url = 'https://foo.com/');
        Config::set('live.order.summary', $summary = 'order-id={order}');

        $toTwilio = (new OrderPendingApprovalSmsLinkNotification($order))->toTwilio(null);

        $prefix   = 'Bluon - ';
        $suffix   = ' - Do Not Reply to this text.';
        $smsText  = $prefix . $url . Str::replace('{order}', $routeKey, $summary) . $suffix;
        $expected = (new TwilioSmsMessage())->content($smsText);

        $this->assertInstanceOf(TwilioSmsMessage::class, $toTwilio);
        $this->assertEquals($expected, $toTwilio);
    }
}
