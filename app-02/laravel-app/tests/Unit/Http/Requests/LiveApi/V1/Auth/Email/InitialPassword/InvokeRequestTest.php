<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Auth\Email\InitialPassword;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\Auth\Email\InitialPassword\InvokeRequest;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

class InvokeRequestTest extends RequestTestCase
{
    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function it_requires_a_password()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PASSWORD]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::PASSWORD])]);
    }

    /** @test */
    public function its_password_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PASSWORD => ['array value']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PASSWORD]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::PASSWORD])]);
    }

    /** @test */
    public function its_password_must_be_at_least_8_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PASSWORD => '1234567']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PASSWORD]);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => RequestKeys::PASSWORD, 'min' => 8]),
        ]);
    }

    /** @test */
    public function its_password_must_be_confirmed()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PASSWORD => 'password']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PASSWORD]);
        $request->assertValidationMessages([Lang::get('validation.confirmed', ['attribute' => RequestKeys::PASSWORD])]);
    }

    /** @test */
    public function it_requires_a_tos_accepted()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TOS_ACCEPTED]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::TOS_ACCEPTED);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_tos_must_be_accepted()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TOS_ACCEPTED => 'a value']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TOS_ACCEPTED]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::TOS_ACCEPTED);
        $request->assertValidationMessages([Lang::get('validation.accepted', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::PASSWORD              => 'password',
            RequestKeys::PASSWORD_CONFIRMATION => 'password',
            RequestKeys::TOS_ACCEPTED          => 1,
        ]);

        $request->assertValidationPassed();
    }
}
