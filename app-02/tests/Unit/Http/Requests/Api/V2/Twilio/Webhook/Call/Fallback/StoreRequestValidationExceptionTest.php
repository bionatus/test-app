<?php

namespace Tests\Unit\Http\Requests\Api\V2\Twilio\Webhook\Call\Fallback;

use App\Http\Requests\Api\V2\Twilio\Webhook\Call\Fallback\StoreRequestValidationException;
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

        $exception = new StoreRequestValidationException($validator);

        $response = $exception->getResponse();
        $expected = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Say>We are very sorry, currently we are experiencing technical difficulties. Please contact us at a later time.</Say></Response>\n";

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($expected, $response->getContent());
    }
}
