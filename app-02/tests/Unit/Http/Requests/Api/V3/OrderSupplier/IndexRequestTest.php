<?php

namespace Tests\Unit\Http\Requests\Api\V3\OrderSupplier;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\OrderSupplier\IndexRequest;
use App\Rules\Location\Format;
use App\Rules\Location\Latitude;
use App\Rules\Location\Longitude;
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
    public function its_search_string_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => ['an array']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SEARCH_STRING);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_search_string_should_have_2_characters_minimum()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => 'a']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SEARCH_STRING);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => $attribute, 'min' => 2]),
        ]);
    }

    /** @test */
    public function its_search_string_should_have_30_characters_maximum()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SEARCH_STRING => Str::random(31)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SEARCH_STRING]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SEARCH_STRING);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 30]),
        ]);
    }

    /** @test */
    public function its_location_must_have_a_valid_string_format()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::LOCATION => '1.234;-5.567']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::LOCATION]);
        $request->assertValidationMessages([(new Format())->message()]);
    }

    /** @test */
    public function its_location_must_have_a_valid_latitude()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::LOCATION => '91.234,-5.567']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::LOCATION]);
        $request->assertValidationMessages([(new Latitude())->message()]);
    }

    /** @test */
    public function its_location_must_have_a_valid_longitude()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::LOCATION => '1.234,-181.567']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::LOCATION]);
        $request->assertValidationMessages([(new Longitude())->message()]);
    }

    /** @test */
    public function it_pass_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::SEARCH_STRING => 'a string',
            RequestKeys::LOCATION => '1.234,-5.567',
        ]);

        $request->assertValidationPassed();
    }
}
