<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Oem;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\Oem\IndexRequest;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

class IndexRequestTest extends RequestTestCase
{
    protected string $requestClass = IndexRequest::class;

    /** @test */
    public function it_requires_a_model_number()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MODEL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MODEL);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_model_param_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::MODEL => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MODEL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MODEL);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_limit_number_param_from_3_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::MODEL => Str::random(2)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MODEL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MODEL);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => $attribute, 'min' => 3]),
        ]);
    }

    /** @test */
    public function it_should_limit_number_param_to_200_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::MODEL => Str::random(201)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MODEL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::MODEL);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 200]),
        ]);
    }

    /** @test */
    public function it_pass_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::MODEL => 'ae11',
        ]);

        $request->assertValidationPassed();
    }
}
