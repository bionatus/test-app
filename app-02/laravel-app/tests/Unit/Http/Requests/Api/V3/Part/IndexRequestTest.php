<?php

namespace Tests\Unit\Http\Requests\Api\V3\Part;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Part\IndexRequest;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

class IndexRequestTest extends RequestTestCase
{
    protected string $requestClass = IndexRequest::class;

    /** @test */
    public function it_requires_a_part_number()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NUMBER]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::NUMBER])]);
    }

    /** @test */
    public function its_number_param_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NUMBER => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NUMBER]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::NUMBER])]);
    }

    /** @test */
    public function it_should_limit_number_param_from_3_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NUMBER => Str::random(1)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NUMBER]);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => RequestKeys::NUMBER, 'min' => 3]),
        ]);
    }

    /** @test */
    public function it_should_limit_number_param_to_255_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NUMBER => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NUMBER]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => RequestKeys::NUMBER, 'max' => 255]),
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
