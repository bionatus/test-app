<?php

namespace Tests\Unit\Http\Requests\Api\V4\Company;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V4\Company\IndexRequest;
use Lang;
use Str;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

class IndexRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = IndexRequest::class;

    /** @test */
    public function it_may_not_get_a_param()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationPassed();
    }

    /** @test */
    public function its_search_string_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => ['array item']]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);

        $attribute = Str::of(RequestKeys::SEARCH_STRING)->replace('_', ' ');
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_limit_the_search_string_to_255_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
        $attribute = Str::of(RequestKeys::SEARCH_STRING)->replace('_', ' ');
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_should_accept_minimum_3_chars_on_the_search_string()
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
            RequestKeys::SEARCH_STRING => 'a name',
        ]);

        $request->assertValidationPassed();
    }
}
