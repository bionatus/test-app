<?php

namespace Tests\Unit\Http\Requests\Api\V3\AppVersion;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\AppVersion\InvokeRequest;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

class InvokeRequestTest extends RequestTestCase
{
    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function its_version_is_required()
    {
        $request = $this->formRequest($this->requestClass, []);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::VERSION]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::VERSION]),
        ]);
    }

    /** @test */
    public function its_version_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::VERSION => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::VERSION]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => RequestKeys::VERSION]),
        ]);
    }

    /** @test */
    public function its_version_must_be_a_version_number()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::VERSION => 'any string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::VERSION]);
        $request->assertValidationMessages([
            Lang::get('validation.regex', ['attribute' => RequestKeys::VERSION]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::VERSION => '1.2.3']);

        $request->assertValidationPassed();
    }
}
