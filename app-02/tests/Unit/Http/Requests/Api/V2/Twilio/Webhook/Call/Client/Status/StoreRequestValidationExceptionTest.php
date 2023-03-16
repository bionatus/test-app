<?php

namespace Tests\Unit\Http\Requests\Api\V2\Twilio\Webhook\Call\Client\Status;

use App\Http\Requests\Api\V2\Twilio\Webhook\Call\Client\Status\StoreRequestValidationException;
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
        $mock = Mockery::mock(\Illuminate\Contracts\Validation\Validator::class);
        $mock->shouldReceive('errors')->andReturn($errors = ['errors']);

        $exception = new StoreRequestValidationException($mock, [], '', null);

        $response = $exception->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(json_encode($errors), $response->getContent());
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
