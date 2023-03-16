<?php

namespace Tests\Unit\Jobs\Service;

use App;
use App\Handlers\ServiceLogHandler;
use App\Jobs\Service\CreateLog;
use App\Models\ServiceLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class CreateLogTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(CreateLog::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new CreateLog('service-name', Collection::make(), Collection::make(), null);

        $this->assertSame('database', $job->connection);
    }

    /** @test */
    public function it_calls_to_service_log_handler()
    {
        $request  = Collection::make();
        $response = Collection::make();
        $model    = null;

        $serviceLog = Mockery::mock(ServiceLog::class);

        $logEvent = Mockery::mock(ServiceLogHandler::class);
        $logEvent->shouldReceive('log')->withArgs([$request, $response, $model])->once()->andReturn($serviceLog);
        App::bind(ServiceLogHandler::class, fn() => $logEvent);

        $job = new CreateLog('service-name', $request, $response, $model);
        $job->handle();
    }
}
