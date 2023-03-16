<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Part;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\Part\IndexRequest;
use Lang;
use Str;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

class IndexRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = IndexRequest::class;

    /** @test */
    public function it_requires_a_number()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NUMBER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NUMBER);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_number_param_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NUMBER => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NUMBER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NUMBER);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_limit_number_param_from_3_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NUMBER => Str::random(2)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NUMBER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NUMBER);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => $attribute, 'min' => 3]),
        ]);
    }

    /** @test */
    public function it_should_limit_number_param_to_255_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NUMBER => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NUMBER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NUMBER);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_pass_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::NUMBER => 'ae11',
        ]);

        $request->assertValidationPassed();
    }
}
