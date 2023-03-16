<?php

namespace Tests\Unit\Http\Requests\Api\V2\Twilio\Webhook\Call\Complete;

use App\Http\Requests\Api\V2\Twilio\Webhook\Call\Complete\StoreRequestValidationException;
use Illuminate\Contracts\Validation\Validator;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class StoreRequestValidationExceptionTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_sets_its_response()
    {
        $mock = Mockery::mock(Validator::class);
        $mock->shouldReceive('errors')->andReturn($errors = ['errors']);

        $exception = new StoreRequestValidationException($mock);

        $response = $exception->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(json_encode($errors), $response->getContent());
    }
}
