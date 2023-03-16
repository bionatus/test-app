<?php

namespace Tests\Unit\Http\Requests\Api\V3\Order\Approve;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V3\Order\ApproveController;
use App\Http\Requests\Api\V3\Order\Approve\InvokeRequest;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see ApproveController */
class InvokeRequestTest extends RequestTestCase
{
    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function its_name_parameter_can_be_ignored()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationErrorsMissing([RequestKeys::NAME]);
    }

    /** @test */
    public function its_name_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => ['an array']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NAME);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_limit_the_name_parameter_to_50_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => Str::random(51)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NAME);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 50]),
        ]);
    }

    /** @test */
    public function it_passes_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::NAME => 'Fake order',
        ]);

        $request->assertValidationPassed();
    }
}
