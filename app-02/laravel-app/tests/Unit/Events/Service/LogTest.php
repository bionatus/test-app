<?php

namespace Tests\Unit\Events\Service;

use App\Events\Service\Log as LogEvent;
use App\Listeners\Service\CreateLog;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class LogTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(LogEvent::class, [
            CreateLog::class,
        ]);
    }

    /** @test */
    public function it_returns_service_name()
    {
        $user = Mockery::mock(User::class);

        $serviceName = 'serviceName';
        $request     = Collection::make([]);
        $response    = Collection::make([]);

        $event = new LogEvent($serviceName, $request, $response, $user);

        $this->assertSame($serviceName, $event->serviceName());
    }

    /** @test */
    public function it_returns_request()
    {
        $user = Mockery::mock(User::class);

        $serviceName = 'serviceName';
        $request     = Collection::make(['url' => 'fake.url', 'method' => 'post', 'payload' => ['param' => 1]]);
        $response    = Collection::make([]);

        $event = new LogEvent($serviceName, $request, $response, $user);

        $this->assertSame($request, $event->request());
    }

    /** @test */
    public function it_returns_response()
    {
        $user = Mockery::mock(User::class);

        $serviceName = 'serviceName';
        $request     = Collection::make([]);
        $response    = Collection::make(['status' => 200, 'content' => 'fake content']);

        $event = new LogEvent($serviceName, $request, $response, $user);

        $this->assertSame($response, $event->response());
    }

    /** @test
     * @dataProvider modelDataProvider
     */
    public function it_returns_model($withModel)
    {
        $model = null;
        if ($withModel) {
            $model = Mockery::mock($withModel);
        }

        $serviceName = 'serviceName';
        $request     = Collection::make([]);
        $response    = Collection::make([]);

        $event = new LogEvent($serviceName, $request, $response, $model);

        $this->assertSame($model, $event->model());
    }

    public function modelDataProvider(): array
    {
        return [
            [null],
            [User::class],
            [Staff::class],
        ];
    }
}
