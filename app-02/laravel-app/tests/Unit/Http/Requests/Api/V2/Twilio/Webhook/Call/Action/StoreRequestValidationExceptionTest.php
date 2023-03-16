<?php

namespace Tests\Unit\Http\Requests\Api\V2\Twilio\Webhook\Call\Action;

use App\Http\Requests\Api\V2\Twilio\Webhook\Call\Action\StoreRequestValidationException;
use App\Jobs\LogCommunicationRequest;
use App\Models\Call;
use App\Models\Communication;
use Bus;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\CanRefreshDatabase;
use Tests\TestCase;
use Validator;

class StoreRequestValidationExceptionTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_sets_its_response()
    {
        $validator = Validator::make([], []);

        $exception = new StoreRequestValidationException($validator, [], '', null);

        $response = $exception->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Say>We couldn't find an available agent. Please wait a few minutes and try again.</Say></Response>\n";
        $this->assertSame($content, $response->getContent());
    }

    /** @test */
    public function it_sets_completes_the_call_and_logs_the_request()
    {
        Bus::fake([LogCommunicationRequest::class]);

        $validator = Validator::make([], []);

        $call = Mockery::mock(Call::class);
        $call->shouldReceive('complete')->withNoArgs()->once();
        $call->shouldReceive('getAttribute')->with('communication')->andReturn(new Communication());

        new StoreRequestValidationException($validator, [], null, $call);

        Bus::assertDispatched(LogCommunicationRequest::class);
    }
}
