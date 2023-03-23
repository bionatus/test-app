<?php

namespace Tests\Unit\Http\Requests\Api\V4\Account\Company;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V4\Account\CompanyController;
use App\Http\Requests\Api\V4\Account\Company\StoreRequest;
use App\Types\CompanyDataType;
use App\Types\CountryDataType;
use Lang;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\Earth;
use MenaraSolutions\Geographer\State;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see CompanyController */
class StoreRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function its_company_name_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_NAME);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_company_name_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY_NAME => ['array value']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_NAME);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_company_type_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_TYPE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_TYPE);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

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

    /** @test */
    public function its_company_country_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_COUNTRY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_COUNTRY);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_company_country_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY_COUNTRY => ['array value']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_COUNTRY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_COUNTRY);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_company_country_must_be_a_valid_value()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY_COUNTRY => 'invalid country']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_COUNTRY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_COUNTRY);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_company_state_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_STATE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_STATE);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_company_state_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY_STATE => ['array value']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_STATE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_STATE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_company_state_must_be_a_valid_value()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COMPANY_COUNTRY => (new Earth())->getCountries()->first()->code,
            RequestKeys::COMPANY_STATE   => 'invalid',
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_STATE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_STATE);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_company_city_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_CITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_CITY);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_company_city_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY_CITY => ['array value']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_CITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_CITY);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_company_zip_code_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_ZIP_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_ZIP_CODE);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_company_zip_code_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY_ZIP_CODE => ['not string value']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_ZIP_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_ZIP_CODE);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $attribute, 'digits' => '5']),
        ]);
    }

    /** @test */
    public function its_company_zip_code_size_must_be_exactly_5_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY_ZIP_CODE => '123456']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_ZIP_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_ZIP_CODE);
        $request->assertValidationMessages([
            Lang::get('validation.digits', ['attribute' => $attribute, 'digits' => '5']),
        ]);
    }

    /** @test */
    public function its_company_zip_code_can_have_trailing_zeros()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY_ZIP_CODE => '00001']);

        $request->assertValidationErrorsMissing([RequestKeys::COMPANY_ZIP_CODE]);
    }

    /** @test */
    public function its_company_address_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_ADDRESS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_ADDRESS);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_company_address_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY_ADDRESS => ['array value']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY_ADDRESS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COMPANY_ADDRESS);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_company_job_title_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::JOB_TITLE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::JOB_TITLE);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_job_title_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::JOB_TITLE => ['array value']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::JOB_TITLE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::JOB_TITLE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_job_title_should_have_a_valid_value()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::JOB_TITLE => ['invalid'],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::JOB_TITLE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::JOB_TITLE);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_primary_equipment_type_should_be_excluded_if_company_type_is_not_contractor()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => 'invalid',
        ]);

        $request->assertValidationErrorsMissing([RequestKeys::PRIMARY_EQUIPMENT_TYPE]);
    }

    /** @test */
    public function its_primary_equipment_type_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COMPANY_TYPE           => CompanyDataType::TYPE_CONTRACTOR,
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => ['array value'],
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PRIMARY_EQUIPMENT_TYPE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PRIMARY_EQUIPMENT_TYPE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_primary_equipment_should_have_valid_value()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COMPANY_TYPE           => CompanyDataType::TYPE_CONTRACTOR,
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => 'invalid',
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PRIMARY_EQUIPMENT_TYPE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PRIMARY_EQUIPMENT_TYPE);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $this->refreshDatabaseForSingleTest();
        $country = Country::build(CountryDataType::UNITED_STATES);
        /** @var State $state */
        $state = $country->getStates()->first();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COMPANY_NAME           => 'ACME',
            RequestKeys::COMPANY_TYPE           => CompanyDataType::TYPE_OEM,
            RequestKeys::COMPANY_COUNTRY        => $country->code,
            RequestKeys::COMPANY_STATE          => $state->isoCode,
            RequestKeys::COMPANY_CITY           => 'a city',
            RequestKeys::COMPANY_ZIP_CODE       => '01094',
            RequestKeys::COMPANY_ADDRESS        => 'the new address',
            RequestKeys::JOB_TITLE              => CompanyDataType::getJobTitles(CompanyDataType::TYPE_OEM)[0],
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => CompanyDataType::EQUIPMENT_TYPE_COMMERCIAL,
        ]);

        $request->assertValidationPassed();
    }
}
