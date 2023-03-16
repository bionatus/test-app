<?php

namespace Tests\Unit\Http\Requests\Api\V2\Twilio\Webhook\Call;

use App\Http\Requests\Api\V2\Twilio\Webhook\Call\StoreRequestValidationException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Validator;

class StoreRequestValidationExceptionTest extends TestCase
{
    /** @test */
    public function it_sets_its_response()
    {
        $validator = Validator::make([], []);

        $exception = new StoreRequestValidationException($validator);

        $response = $exception->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Say>We couldn't find an available agent. Please wait a few minutes and try again.</Say></Response>\n";
        $this->assertSame($content, $response->getContent());
    }
}
