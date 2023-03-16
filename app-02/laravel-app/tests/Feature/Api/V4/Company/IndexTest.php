<?php

namespace Tests\Feature\Api\V4\Company;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V4\CompanyController;
use App\Http\Requests\Api\V4\Company\IndexRequest;
use App\Http\Resources\Api\V4\Company\BaseResource;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see CompanyController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V4_COMPANY_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $route = URL::route($this->routeName);
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get($route);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, IndexRequest::class);
    }

    /** @test */
    public function it_can_search_for_company_name_by_text()
    {
        Company::factory()->count(2)->create(['name' => 'Another Name']);
        $company = Company::factory()->count(3)->create(['name' => 'Company Special name']);

        $route = URL::route($this->routeName);

        $this->login();
        $response = $this->getWithParameters($route, [RequestKeys::SEARCH_STRING => 'Special']);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $company);

        $data = Collection::make($response->json('data'));

        $firstPageCompanies = $company->sortBy('name')->values()->take(count($data));

        $data->each(function(array $rawCompanies, int $index) use ($firstPageCompanies) {
            $company = $firstPageCompanies->get($index);
            $this->assertSame($company->getRouteKey(), $rawCompanies['id']);
        });
    }
}
