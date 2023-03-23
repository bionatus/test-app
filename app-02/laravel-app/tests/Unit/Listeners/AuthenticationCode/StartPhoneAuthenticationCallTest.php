<?php

namespace Tests\Unit\Listeners\AuthenticationCode;

use App;
use App\Events\AuthenticationCode\CallRequested;
use App\Jobs\PhoneAuthenticationCall;
use App\Listeners\AuthenticationCode\StartPhoneAuthenticationCall;
use App\Models\Phone;
use Bus;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use ReflectionClass;
use ReflectionProperty;
use Tests\TestCase;

class StartPhoneAuthenticationCallTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(StartPhoneAuthenticationCall::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_start_a_phone_call()
    {
        Bus::fake([PhoneAuthenticationCall::class]);

        $phone = Phone::factory()->make();

        $event = new CallRequested($phone);

        $listener = App::make(StartPhoneAuthenticationCall::class);
        $listener->handle($event);
        Bus::assertDispatched(PhoneAuthenticationCall::class, function(PhoneAuthenticationCall $job) use ($phone) {
            $property = new ReflectionProperty(PhoneAuthenticationCall::class, 'phone');
            $property->setAccessible(true);

            /** @var Phone $phone */
            $jobPhone = $property->getValue($job);

            $this->assertEquals($phone->getKey(), $jobPhone->getKey());

            return true;
        });
    }
}
