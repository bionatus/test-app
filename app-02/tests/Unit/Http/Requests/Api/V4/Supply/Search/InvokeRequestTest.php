<?php

namespace Tests\Unit\Http\Requests\Api\V4\Supply\Search;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V4\Supply\Search\InvokeRequest;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

class InvokeRequestTest extends RequestTestCase
{
    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function it_requires_a_supply_name()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::NAME])]);
    }

    /** @test */
    public function its_name_param_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => ['cable']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::NAME])]);
    }

    /** @test */
    public function it_should_limit_number_param_from_3_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => Str::random(2)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => RequestKeys::NAME, 'min' => 3]),
        ]);
    }

    /** @test */
    public function it_should_limit_name_param_to_255_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => RequestKeys::NAME, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_pass_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::NAME => 'cable',
        ]);

        $request->assertValidationPassed();
    }
}
