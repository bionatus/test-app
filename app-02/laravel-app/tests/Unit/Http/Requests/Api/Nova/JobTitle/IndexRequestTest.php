<?php

namespace Tests\Unit\Http\Requests\Api\Nova\JobTitle;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\Nova\JobTitle\IndexRequest;
use App\Types\CompanyDataType;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

class IndexRequestTest extends RequestTestCase
{
    protected string $requestClass = IndexRequest::class;

    /** @test */
    public function its_company_type_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY_TYPE => ['array value']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_TYPE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_TYPE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_company_type_must_be_valid()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY_TYPE => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_TYPE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_TYPE);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => $attribute])]);
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_should_pass_on_valid_values(string $companyType)
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY_TYPE => $companyType]);

        $request->assertValidationPassed();
    }

    public function dataProvider()
    {
        return [
            [CompanyDataType::TYPE_CONTRACTOR],
            [CompanyDataType::TYPE_SUPPLY_HOUSE],
            [CompanyDataType::TYPE_TRADE_SCHOOL],
            [CompanyDataType::TYPE_OEM],
            [CompanyDataType::TYPE_PROPERTY_MANAGER_OWNER],
        ];
    }
}
