<?php

namespace Tests\Unit\Http\Requests\Api\V3\User;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\User\IndexRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

class IndexRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = IndexRequest::class;

    /** @test */
    public function it_requires_a_search_string()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SEARCH_STRING);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_search_string_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SEARCH_STRING);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_limit_number_param_from_3_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => Str::random(2)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SEARCH_STRING);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => $attribute, 'min' => 3]),
        ]);
    }

    /** @test */
    public function it_pass_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::SEARCH_STRING => 'Foo',
        ]);

        $request->assertValidationPassed();
    }
}
