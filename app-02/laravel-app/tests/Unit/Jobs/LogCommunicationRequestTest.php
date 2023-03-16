<?php

namespace Tests\Unit\Jobs;

use App\Jobs\LogCommunicationRequest;
use App\Models\Call;
use App\Models\Communication;
use App\Models\CommunicationLog;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogCommunicationRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_does_nothing_if_not_enabled_by_config()
    {
        Config::set('communications.log_requests', false);

        $job = new LogCommunicationRequest(new Communication(), '', [], '', null);
        $job->handle();

        $this->assertDatabaseCount(CommunicationLog::tableName(), 0);
    }

    /** @test */
    public function it_stores_a_communication_log()
    {
        Config::set('communications.log_requests', true);
        $routeName   = 'a route name';
        $call        = Call::factory()->create();
        $description = 'Description';
        $parameter   = 'A parameter';
        $error       = 'A error';
        $payload     = [
            'a_parameter' => $parameter,
        ];
        $errors      = [
            'a_error' => $error,
        ];
        $response    = 'a response';

        $job = new LogCommunicationRequest($call->communication, $description, $payload, $response, $routeName,
            $errors);
        $job->handle();

        $this->assertDatabaseCount(CommunicationLog::tableName(), 1);
        $this->assertDatabaseHas(CommunicationLog::tableName(), [
            'communication_id'     => $call->communication->getKey(),
            'description'          => $description,
            'request->a_parameter' => $parameter,
            'response'             => $response,
            'source'               => $routeName,
            'errors->a_error'      => $error,
        ]);
    }
}
