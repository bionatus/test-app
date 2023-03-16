<?php

namespace Tests\Feature\Api\V2;

use App\Http\Middleware\ValidateTwilioRequest;
use JMac\Testing\Traits\AdditionalAssertions;

trait WithTwilioMiddlewares
{
    use AdditionalAssertions;

    /** @test */
    public function it_uses_twilio_guard()
    {
        $this->assertRouteUsesMiddleware($this->routeName, [ValidateTwilioRequest::class]);
    }
}

