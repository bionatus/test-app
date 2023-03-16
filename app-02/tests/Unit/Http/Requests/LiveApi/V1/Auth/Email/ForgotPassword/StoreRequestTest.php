<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Auth\Email\ForgotPassword;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\Auth\Email\ForgotPassword\StoreRequest;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

class StoreRequestTest extends RequestTestCase
{
    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function it_requires_an_email()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EMAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::EMAIL);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_email_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::EMAIL => ['array value']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EMAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::EMAIL);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_email_must_be_a_valid_email()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::EMAIL => 'invalid @email.com']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EMAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::EMAIL);
        $request->assertValidationMessages([Lang::get('validation.email', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_email_must_end_with_a_valid_top_level_domain()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::EMAIL => 'email@email.invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::EMAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::EMAIL);
        $message   = Str::replace([':attribute'], [$attribute], 'The :attribute field does not end with a valid tld.');
        $request->assertValidationMessages([$message]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::EMAIL => 'user@email.com',
        ]);

        $request->assertValidationPassed();
    }
}
