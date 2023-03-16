<?php

namespace Tests\Unit\Http\Requests\Api\V4\Order\Name;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V4\Order\NameController;
use App\Http\Requests\Api\V4\Order\Name\InvokeRequest;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see NameController */
class InvokeRequestTest extends RequestTestCase
{
    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function its_name_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::NAME]),
        ]);
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
    public function it_should_limit_the_name_parameter_from_2_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => Str::random(1)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NAME);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => $attribute, 'min' => 2]),
        ]);
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
