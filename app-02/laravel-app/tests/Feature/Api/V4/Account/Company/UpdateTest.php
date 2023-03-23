<?php

namespace Tests\Feature\Api\V4\Account\Company;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V4\Account\CompanyController;
use App\Http\Requests\Api\V4\Account\Company\UpdateRequest;
use App\Http\Resources\Api\V4\Account\Company\BaseResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use App\Types\CompanyDataType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see CompanyController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string  $routeName = RouteNames::API_V4_ACCOUNT_COMPANY_UPDATE;
    private Company $company;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();
        $this->expectException(UnauthorizedHttpException::class);
        $this->patch(URL::route($this->routeName, [$this->company]));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, UpdateRequest::class);
    }

    /** @test */
    public function it_creates_a_user_company_relation()
    {
        $user = User::factory()->create();
        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->patch($route, [
            RequestKeys::COMPANY                => $this->company->getRouteKey(),
            RequestKeys::JOB_TITLE              => $jobTitle = CompanyDataType::getJobTitles($this->company->type)[0],
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => $primaryEquipmentType = CompanyDataType::EQUIPMENT_TYPE_RESIDENTIAL,
        ]);
        $response->assertStatus(Response::HTTP_OK);

        $schema = $this->jsonSchema(BaseResource::jsonSchema());
        $this->validateResponseSchema($schema, $response);

        $this->assertDatabaseHas(CompanyUser::tableName(), [
            'user_id'        => $user->getKey(),
            'company_id'     => $this->company->getKey(),
            'job_title'      => $jobTitle,
            'equipment_type' => $primaryEquipmentType,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create([
            'address' => 'Street 123',
        ]);
    }
}
