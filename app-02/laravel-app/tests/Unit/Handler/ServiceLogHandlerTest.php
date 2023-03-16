<?php

namespace Tests\Unit\Handler;

use App\Handlers\ServiceLogHandler;
use App\Models\ServiceLog;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ServiceLogHandlerTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * @dataProvider modelDataProvider
     */
    public function it_creates_a_service_log($modelClass)
    {
        $model = $causerId = $causerType = null;

        if (null !== $modelClass) {
            $model      = $modelClass::factory()->createQuietly();
            $causerId   = $model->getKey();
            $causerType = $model->getMorphClass();
        }

        $serviceName = 'service-name';
        $request     = Collection::make([
            'method'  => $method = 'post',
            'url'     => $url = 'http://fake.url.com',
            'payload' => ['params' => ['param1' => true, 'param2' => 'fake parameter']],
        ]);
        $response    = Collection::make([
            'status'  => $status = 200,
            'content' => json_encode(['data' => ['content' => 'fake content']]),
        ]);

        $handler = new ServiceLogHandler($serviceName);
        $handler->log($request, $response, $model);

        $this->assertDatabaseHas(ServiceLog::tableName(), [
            'causer_id'                       => $causerId,
            'causer_type'                     => $causerType,
            'request_method'                  => $method,
            'request_url'                     => $url,
            'request_payload->params->param1' => true,
            'request_payload->params->param2' => 'fake parameter',
            'response_status'                 => $status,
            'response_content->data->content' => 'fake content',
        ]);
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
