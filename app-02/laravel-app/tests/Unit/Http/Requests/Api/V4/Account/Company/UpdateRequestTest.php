<?php

namespace Tests\Unit\Http\Requests\Api\V4\Account\Company;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V4\Account\CompanyController;
use App\Http\Requests\Api\V4\Account\Company\UpdateRequest;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use App\Types\CompanyDataType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see CompanyController */
class UpdateRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string  $requestClass = UpdateRequest::class;
    protected Company $company;
    protected User    $user;
    protected string  $companyType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company     = Company::factory()->create(['type' => CompanyDataType::TYPE_CONTRACTOR]);
        $this->companyType = $this->company->type;
        $this->user        = User::factory()->create();
        CompanyUser::factory()->usingUser($this->user)->usingCompany($this->company)->create();
    }

    /** @test */
    public function its_company_is_required()
    {
        $request = $this->formRequest($this->requestClass);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::COMPANY]),
        ]);
    }

    /** @test */
    public function its_company_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COMPANY => 123123]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => RequestKeys::COMPANY]),
        ]);
    }

    /** @test */
    public function its_company_should_be_exist()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COMPANY => '123123',
        ]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COMPANY]);
        $request->assertValidationMessages([
            Lang::get('validation.exists', ['attribute' => RequestKeys::COMPANY]),
        ]);
    }

    /** @test */
    public function its_company_job_title_is_required()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COMPANY => $this->company->getRouteKey(),
        ]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::JOB_TITLE]);
        $attribute = Str::of(RequestKeys::JOB_TITLE)->replace('_', ' ');
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_company_job_title_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COMPANY   => $this->company->getRouteKey(),
            RequestKeys::JOB_TITLE => ['array value'],
        ]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::JOB_TITLE]);
        $attribute = Str::of(RequestKeys::JOB_TITLE)->replace('_', ' ');
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_company_job_title_should_have_a_valid_value()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COMPANY   => $this->company->getRouteKey(),
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
        $company = Company::factory()->create(['type' => CompanyDataType::TYPE_OEM]);
        $user    = User::factory()->create();
        CompanyUser::factory()->usingUser($user)->usingCompany($company)->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COMPANY                => $company->getRouteKey(),
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => 'invalid',
        ]);
        $request->assertValidationErrorsMissing([RequestKeys::PRIMARY_EQUIPMENT_TYPE]);
    }

    /** @test */
    public function its_primary_equipment_type_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COMPANY                => $this->company->getRouteKey(),
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => 123123,
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
            RequestKeys::COMPANY                => $this->company->getRouteKey(),
            RequestKeys::JOB_TITLE              => CompanyDataType::getJobTitles($this->companyType)[0],
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
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COMPANY                => $this->company->getRouteKey(),
            RequestKeys::JOB_TITLE              => CompanyDataType::getJobTitles($this->companyType)[0],
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => CompanyDataType::EQUIPMENT_TYPE_COMMERCIAL,
        ]);

        $request->assertValidationPassed();
    }
}
