<?php

namespace Tests\Unit\Http\Requests\Api\V2\Twilio\Token;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V2\Twilio\Token\StoreRequest;
use Lang;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see TokenController */
class StoreRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function it_requires_an_os()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::OS]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::OS])]);
    }

    /** @test */
    public function its_os_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::OS => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::OS]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::OS])]);
    }

    /** @test */
    public function it_os_must_be_valid()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::OS => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::OS]);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => RequestKeys::OS])]);
    }
}
