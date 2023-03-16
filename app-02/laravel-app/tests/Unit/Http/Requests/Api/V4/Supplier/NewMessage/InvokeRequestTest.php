<?php

namespace Tests\Unit\Http\Requests\Api\V4\Supplier\NewMessage;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V4\Supplier\NewMessage\InvokeRequest;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

class InvokeRequestTest extends RequestTestCase
{
    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function it_requires_a_message_parameter()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MESSAGE]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::MESSAGE])]);
    }

    /** @test */
    public function its_message_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::MESSAGE => 123]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MESSAGE]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::MESSAGE])]);
    }

    /** @test */
    public function it_passes_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::MESSAGE => 'This is a test message',
        ]);

        $request->assertValidationPassed();
    }
}
