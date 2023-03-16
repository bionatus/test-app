<?php

namespace Tests\Unit\Http\Requests\Api\V3\Account\Term\Accept;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Account\Term\Accept\InvokeRequest;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

class InvokeRequestTest extends RequestTestCase
{
    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function it_requires_an_acceptance()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TOS_ACCEPTED]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::TOS_ACCEPTED);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_term_acceptance_must_be_accepted()
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
            RequestKeys::TOS_ACCEPTED => 1,
        ]);

        $request->assertValidationPassed();
    }
}
