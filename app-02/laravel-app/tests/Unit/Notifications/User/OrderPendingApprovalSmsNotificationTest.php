<?php

namespace Tests\Unit\Notifications\User;

use App;
use App\Actions\Models\Order\CalculatePoints;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\User\OrderPendingApprovalSmsNotification;
use App\Types\Point;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use ReflectionClass;
use Tests\TestCase;

class OrderPendingApprovalSmsNotificationTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(OrderPendingApprovalSmsNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $notification = new OrderPendingApprovalSmsNotification(new Order());

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

        $notification = new OrderPendingApprovalSmsNotification($order);

        $this->assertSame($expected, in_array(TwilioChannel::class, $notification->via(null)));
    }

    public function pushNotifications(): array
    {
        return [[true], [false]];
    }

    /** @test */
    public function it_sets_twilio_message_with_correct_order_points()
    {
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($supplierName = 'name');

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->withArgs(['supplier'])->once()->andReturn($supplier);

        $pointData = Mockery::mock(Point::class);
        $pointData->shouldReceive('points')->withNoArgs()->once()->andReturn($points = 1002);

        $calculatePoints = Mockery::mock(CalculatePoints::class);
        $calculatePoints->shouldReceive('execute')->withNoArgs()->once()->andReturn($pointData);
        App::bind(CalculatePoints::class, fn() => $calculatePoints);

        $toTwilio = (new OrderPendingApprovalSmsNotification($order))->toTwilio(null);

        $smsText  = "Bluon - Quote Alert! Be sure to approve the Quote from $supplierName to receive $points Bluon Points.\nDo not reply to this text.";
        $expected = (new TwilioSmsMessage())->content($smsText);

        $this->assertInstanceOf(TwilioSmsMessage::class, $toTwilio);
        $this->assertEquals($expected, $toTwilio);
    }
}
