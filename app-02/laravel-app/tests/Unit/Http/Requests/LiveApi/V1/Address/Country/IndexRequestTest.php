<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Address\Country;

use App\Constants\Locales;
use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\Address\Country\IndexRequest;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

class IndexRequestTest extends RequestTestCase
{
    protected string $requestClass = IndexRequest::class;

    /** @test */
    public function its_locale_must_a_valid_be_value()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::LOCALE => 'a string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::LOCALE]);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => RequestKeys::LOCALE])]);
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_should_pass_on_valid_values($locale)
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::LOCALE => $locale]);

        $request->assertValidationPassed();
    }

    public function dataProvider()
    {
        return [
            'english' => [Locales::EN],
            'spanish' => [Locales::ES],
        ];
    }
}
