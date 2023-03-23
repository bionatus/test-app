<?php

namespace Tests\Unit\Http\Requests\Api\V3\Supplier;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Supplier\IndexRequest;
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
    public function it_should_limit_the_search_string_to_1000_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => Str::random(1001)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
        $attribute = Str::of(RequestKeys::SEARCH_STRING)->replace('_', ' ');
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 1000]),
        ]);
    }

    /** @test */
    public function its_zip_code_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ZIP_CODE => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ZIP_CODE]);
        $attribute = Str::of(RequestKeys::ZIP_CODE)->replace('_', ' ');
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_zip_code_must_be_exactly_5_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ZIP_CODE => '1001']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ZIP_CODE]);
        $attribute = Str::of(RequestKeys::ZIP_CODE)->replace('_', ' ');
        $request->assertValidationMessages([
            Lang::get('validation.digits', ['attribute' => $attribute, 'digits' => 5]),
        ]);
    }

    /** @test */
    public function its_zip_code_can_have_trailing_zeros()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ZIP_CODE => '00001']);

        $request->assertValidationErrorsMissing([RequestKeys::ZIP_CODE]);
    }

    /** @test */
    public function it_pass_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::SEARCH_STRING => 'an address',
            RequestKeys::ZIP_CODE      => '00000',
        ]);

        $request->assertValidationPassed();
    }
}
