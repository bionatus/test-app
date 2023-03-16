<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Supplier\User;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\Supplier\User\IndexRequest;
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
        $attribute = $this->getDisplayableAttribute(RequestKeys::SEARCH_STRING);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_limit_number_param_from_2_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => Str::random(1)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SEARCH_STRING);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => $attribute, 'min' => 2]),
        ]);
    }

    /** @test */
    public function it_should_limit_the_search_string_to_50_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => Str::random(51)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SEARCH_STRING);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 50]),
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
