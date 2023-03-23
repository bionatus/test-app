<?php

namespace Tests\Unit\Notifications\Phone;

use App\Channels\SmsChannel;
use App\Models\AuthenticationCode;
use App\Notifications\Phone\SmsRequestedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class SmsRequestedNotificationTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SmsRequestedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_sync_queue()
    {
        $notification = new SmsRequestedNotification(new AuthenticationCode());

        $this->assertSame('sync', $notification->connection);
    }

    /** @test */
    public function it_is_sent_via_sms_channel()
    {
        $notification = new SmsRequestedNotification(new AuthenticationCode());

        $this->assertSame([SmsChannel::class], $notification->via());
    }

    /** @test
     * @dataProvider messageProvider
     */
    public function it_has_a_correct_message(bool $isLogin, string $type)
    {
        $code = '012345';

        $authenticationCode = Mockery::mock(AuthenticationCode::class);
        $authenticationCode->shouldReceive('getAttribute')->withArgs(['code'])->once()->andReturn($code);
        $authenticationCode->shouldReceive('isLogin')->withNoArgs()->once()->andReturn($isLogin);
        $notification = new SmsRequestedNotification($authenticationCode);

        $expected = "Your Bluon {$type} code is: {$code}";

        $this->assertSame($expected, $notification->toSms());
    }

    public function messageProvider(): array
    {
        return [
            [false, 'verification'],
            [true, 'login'],
        ];
    }
}
