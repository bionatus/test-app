<?php

namespace Tests\Feature\Api\V4\Account\Company;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V4\Account\CompanyController;
use App\Http\Requests\Api\V4\Account\Company\StoreRequest;
use App\Http\Resources\Api\V4\Account\Company\BaseResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Scopes\ByUuid;
use App\Models\User;
use App\Types\CompanyDataType;
use App\Types\CountryDataType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\State;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see CompanyController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V4_ACCOUNT_COMPANY_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();
        $this->expectException(UnauthorizedHttpException::class);
        $this->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    public function CompanyProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @test
     * @dataProvider CompanyProvider
     */
    public function it_creates_a_company_and_a_user_company_relation(bool $userAlreadyHasCompany)
    {
        $user = User::factory()->create();
        if ($userAlreadyHasCompany) {
            $company                 = Company::factory()->create();
            $companyUser             = new CompanyUser();
            $companyUser->user_id    = $user->getKey();
            $companyUser->company_id = $company->getKey();
        }
        $this->login($user);
        $country = Country::build(CountryDataType::UNITED_STATES);
        /** @var State $state */
        $state    = $country->getStates()->first();
        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::COMPANY_NAME           => $companyName = 'company',
            RequestKeys::COMPANY_TYPE           => $companyType = CompanyDataType::TYPE_CONTRACTOR,
            RequestKeys::COMPANY_COUNTRY        => $companyCountryCode = $country->code,
            RequestKeys::COMPANY_STATE          => $companyStateCode = $state->isoCode,
            RequestKeys::COMPANY_CITY           => $companyCity = 'La Plata',
            RequestKeys::COMPANY_ZIP_CODE       => $companyPostCode = '12345',
            RequestKeys::COMPANY_ADDRESS        => $companyPostAddress = 'the new address',
            RequestKeys::JOB_TITLE              => $jobTitle = CompanyDataType::getJobTitles(CompanyDataType::TYPE_CONTRACTOR)[0],
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => $primaryEquipmentType = CompanyDataType::EQUIPMENT_TYPE_RESIDENTIAL,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $newCompanyUuid = $response->json('data.company.id');
        $newCompany     = Company::scoped(new ByUuid($newCompanyUuid))->first();

        $this->assertDatabaseHas(Company::tableName(), [
            'uuid'     => $newCompanyUuid,
            'name'     => $companyName,
            'type'     => $companyType,
            'country'  => $companyCountryCode,
            'state'    => $companyStateCode,
            'city'     => $companyCity,
            'zip_code' => $companyPostCode,
            'address'  => $companyPostAddress,
        ]);

        $this->assertDatabaseHas(CompanyUser::tableName(), [
            'user_id'        => $user->getKey(),
            'job_title'      => $jobTitle,
            'equipment_type' => $primaryEquipmentType,
            'company_id'     => $newCompany->getKey(),
        ]);

        $schema = $this->jsonSchema(BaseResource::jsonSchema());
        $this->validateResponseSchema($schema, $response);
    }
}
