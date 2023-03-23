<?php

namespace Tests\Unit\Listeners\Service;

use App\Events\Service\Log;
use App\Jobs\Service\CreateLog as CreateLogJob;
use App\Listeners\Service\CreateLog;
use Bus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class CreateLogTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(CreateLog::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_dispatches_a_create_log_job()
    {
        Bus::fake();

        $logEvent = Mockery::mock(Log::class);
        $logEvent->shouldReceive('serviceName')->withNoArgs()->once();
        $logEvent->shouldReceive('request')->withNoArgs()->once();
        $logEvent->shouldReceive('response')->withNoArgs()->once();
        $logEvent->shouldReceive('model')->withNoArgs()->once();

        $listener = new CreateLog();
        $listener->handle($logEvent);

        Bus::assertDispatched(CreateLogJob::class);
    }
}
