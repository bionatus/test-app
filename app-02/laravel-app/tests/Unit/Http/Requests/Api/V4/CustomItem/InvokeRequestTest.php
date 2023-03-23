<?php

namespace Tests\Unit\Http\Requests\Api\V4\CustomItem;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V4\CustomItem\InvokeRequest;
use Lang;
use Str;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

class InvokeRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function it_requires_a_name()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::NAME]),
        ]);
    }

    /** @test */
    public function its_name_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => ['array value']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => RequestKeys::NAME]),
        ]);
    }

    /** @test */
    public function its_name_must_have_at_least_3_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => 'ab']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => RequestKeys::NAME, 'min' => 3]),
        ]);
    }

    /** @test */
    public function its_name_must_be_at_most_40_characters_long()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => Str::random(41)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => RequestKeys::NAME, 'max' => 40]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::NAME => 'a valid custom item',
        ]);

        $request->assertValidationPassed();
    }
}
