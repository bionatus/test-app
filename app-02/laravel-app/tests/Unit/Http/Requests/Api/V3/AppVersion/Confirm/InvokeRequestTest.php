<?php

namespace Tests\Unit\Http\Requests\Api\V3\AppVersion\Confirm;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\AppVersion\Confirm\InvokeRequest;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

class InvokeRequestTest extends RequestTestCase
{
    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function its_seconds_is_required()
    {
        $request = $this->formRequest($this->requestClass, []);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SECONDS]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::SECONDS]),
        ]);
    }

    /** @test */
    public function its_version_must_be_a_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SECONDS => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SECONDS]);
        $request->assertValidationMessages([
            Lang::get('validation.integer', ['attribute' => RequestKeys::SECONDS]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SECONDS => 5]);

        $request->assertValidationPassed();
    }
}
