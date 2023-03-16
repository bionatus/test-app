<?php

namespace Tests\Unit\Notifications\User;

use App\Models\Order;
use App\Models\User;
use App\Notifications\User\AssignSmsNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use ReflectionClass;
use Tests\TestCase;

class AssignSmsNotificationTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(AssignSmsNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $notification = new AssignSmsNotification(new Order());

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

        $notification = new AssignSmsNotification($order);

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
        $order->shouldReceive('getAttribute')
            ->withArgs(['working_on_it'])
            ->once()
            ->andReturn($workingOnIt = 'working_on_it');

        $toTwilio = (new AssignSmsNotification($order))->toTwilio(null);

        $smsText  = "Bluon - $workingOnIt is working on your quote. Stay tuned! Do Not Reply to this text.";
        $expected = (new TwilioSmsMessage())->content($smsText);

        $this->assertInstanceOf(TwilioSmsMessage::class, $toTwilio);
        $this->assertEquals($expected, $toTwilio);
    }
}
