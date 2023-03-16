<?php

namespace Tests\Unit\Listeners\Phone;

use App;
use App\Events\Phone\Verified;
use App\Jobs\Phone\RemoveVerifiedUnassigned;
use App\Listeners\Phone\DelayRemoveVerifiedUnassignedJob;
use App\Models\Phone;
use Bus;
use Carbon\CarbonImmutable;
use Config;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use ReflectionProperty;
use Tests\TestCase;

class DelayRemoveVerifiedUnassignedJobTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(DelayRemoveVerifiedUnassignedJob::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_dispatch_a_job_and_delay_its_execution()
    {
        $ttl = 10;
        Config::set('communications.phone.verification.ttl', $ttl);
        $now   = CarbonImmutable::now();
        $delay = $now->addMinutes($ttl);

        Bus::fake([RemoveVerifiedUnassigned::class]);

        $event = Mockery::mock(Verified::class);
        $phone = Mockery::mock(Phone::class);
        $phone->shouldReceive('getAttribute')->withArgs(['created_at'])->once()->andReturn($now);

        $event->shouldReceive('phone')->withNoArgs()->once()->andReturn($phone);

        $listener = App::make(DelayRemoveVerifiedUnassignedJob::class);
        $listener->handle($event);

        Bus::assertDispatched(RemoveVerifiedUnassigned::class,
            function(RemoveVerifiedUnassigned $job) use ($phone, $delay, $now) {
                $property = new ReflectionProperty($job, 'phone');
                $property->setAccessible(true);
                $this->assertSame($phone, $property->getValue($job));

                $this->assertTrue($job->delay->eq($delay));

                return true;
            });
    }
}
