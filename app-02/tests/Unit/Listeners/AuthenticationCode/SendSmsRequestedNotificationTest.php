<?php

namespace Tests\Unit\Listeners\AuthenticationCode;

use App;
use App\Events\AuthenticationCode\SmsRequested;
use App\Listeners\AuthenticationCode\SendSmsRequestedNotification;
use App\Models\AuthenticationCode;
use App\Models\Phone;
use App\Notifications\Phone\SmsRequestedNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Notification;
use ReflectionClass;
use ReflectionProperty;
use Tests\TestCase;

class SendSmsRequestedNotificationTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendSmsRequestedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_sends_a_notification_to_a_phone()
    {
        Notification::fake();

        $phone              = Phone::factory()->make(['id' => $id = 1]);
        $authenticationCode = AuthenticationCode::factory()->make(['phone_id' => $id]);

        $authenticationCode->setRelation('phone', $phone);

        $event = new SmsRequested($authenticationCode);

        $listener = App::make(SendSmsRequestedNotification::class);
        $listener->handle($event);
        Notification::assertSentTo($phone, SmsRequestedNotification::class,
            function(SmsRequestedNotification $notification) use ($phone) {
                $property = new ReflectionProperty(SmsRequestedNotification::class, 'authenticationCode');
                $property->setAccessible(true);

                /** @var AuthenticationCode $authenticationCode */
                $authenticationCode = $property->getValue($notification);

                $this->assertEquals($phone->getKey(), $authenticationCode->phone_id);

                return true;
            });
    }
}
