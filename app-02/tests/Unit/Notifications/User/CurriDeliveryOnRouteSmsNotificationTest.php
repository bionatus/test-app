<?php

namespace Tests\Unit\Notifications\User;

use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\User\CurriDeliveryOnRouteSmsNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

class CurriDeliveryOnRouteSmsNotificationTest extends TestCase
{
    /** @test
     * @throws ReflectionException
     */
    public function it_uses_trait()
    {
        $this->assertUseTrait(CurriDeliveryOnRouteSmsNotification::class, Queueable::class);
    }

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(CurriDeliveryOnRouteSmsNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $notification = new CurriDeliveryOnRouteSmsNotification(new Order());

        $this->assertEquals('database', $notification->connection);
    }

    /** @test
     * @dataProvider viaProvider
     */
    public function it_can_notify_via_sms(bool $expected)
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('shouldSendSmsNotificationWithoutSetting')->withNoArgs()->once()->andReturn($expected);

        $notification = new CurriDeliveryOnRouteSmsNotification(new Order());

        $this->assertSame($expected, in_array(TwilioChannel::class, $notification->via($user)));
    }

    public function viaProvider(): array
    {
        return [[true], [false]];
    }

    /** @test */
    public function it_sets_twilio_message()
    {
        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getAttribute')->with('name')->once()->andReturn($supplierName = 'supplier name');

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('supplier')->once()->andReturn($supplier);

        $twilioSmsMessage = (new CurriDeliveryOnRouteSmsNotification($order))->toTwilio();

        $message  = "Bluon - $supplierName sent you a message: Your driver is on the way! Do Not Reply to this text.";
        $expected = (new TwilioSmsMessage())->content($message);

        $this->assertInstanceOf(TwilioSmsMessage::class, $twilioSmsMessage);
        $this->assertEquals($expected, $twilioSmsMessage);
    }
}
