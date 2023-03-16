<?php

namespace Tests\Feature\Api\V3\Supplier;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\SupplierController;
use App\Http\Requests\Api\V3\Supplier\IndexRequest;
use App\Http\Resources\Api\V3\Supplier\BaseResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Types\CountryDataType;
use Exception;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see SupplierController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_SUPPLIER_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, IndexRequest::class);
    }

    /** @test */
    public function it_display_a_list_of_suppliers_ordered_alphabetically_when_company_zip_code_is_outside_us()
    {
        $company     = Company::factory()->create(['country' => 'NON-US']);
        $companyUser = CompanyUser::factory()->usingCompany($company)->create();

        $route = URL::route($this->routeName);

        $suppliers = Supplier::factory()->count(20)->sequence(fn(Sequence $sequence) => [
            'name' => Str::lower(Str::random(10)),
        ])->createQuietly()->sortBy(function(Supplier $supplier) {
            return strtolower($supplier->name);
        });

        $this->login($companyUser->user);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $suppliers);
        $data               = Collection::make($response->json('data'));
        $firstPageSuppliers = $suppliers->values()->take(count($data));

        $data->each(function(array $rawSupplier, int $index) use ($firstPageSuppliers) {
            $supplier = $firstPageSuppliers->get($index);
            $this->assertSame($supplier->getRouteKey(), $rawSupplier['id']);
        });
    }

    /** @test */
    public function it_display_a_list_of_suppliers_ordered_alphabetically_when_company_has_no_valid_coordinates()
    {
        $company     = Company::factory()->create(['country' => 'US']);
        $companyUser = CompanyUser::factory()->usingCompany($company)->create();

        $route = URL::route($this->routeName);

        $suppliers = Supplier::factory()->count(20)->sequence(fn(Sequence $sequence) => [
            'name' => Str::lower(Str::random(10)),
        ])->createQuietly()->sortBy(function(Supplier $supplier) {
            return strtolower($supplier->name);
        });

        $this->login($companyUser->user);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $suppliers);
        $data               = Collection::make($response->json('data'));
        $firstPageSuppliers = $suppliers->values()->take(count($data));

        $data->each(function(array $rawSupplier, int $index) use ($firstPageSuppliers) {
            $supplier = $firstPageSuppliers->get($index);
            $this->assertSame($supplier->getRouteKey(), $rawSupplier['id']);
        });
    }

    /** @test */
    public function it_display_a_list_of_suppliers_ordered_by_preferred_and_distance_then_alphabetically_when_company_zip_code_is_in_us(
    )
    {
        $company     = Company::factory()->createQuietly([
            'country'   => CountryDataType::UNITED_STATES,
            'zip_code'  => '90210',
            'latitude'  => 0,
            'longitude' => 0,
        ]);
        $companyUser = CompanyUser::factory()->usingCompany($company)->create();

        $route = URL::route($this->routeName);

        $suppliers = Supplier::factory()->createManyQuietly([
            [
                'name'      => 'B',
                'latitude'  => '1.2',
                'longitude' => '1.2',
            ],
            [
                'name'      => 'A',
                'latitude'  => '1.2',
                'longitude' => '1.2',
            ],
            [
                'latitude'  => '0',
                'longitude' => '0',
            ],
            [
                'latitude'  => '1',
                'longitude' => '1',
            ],
        ]);

        $ordered = $suppliers->sortBy([
            ['latitude', 'asc'],
            ['name', 'asc'],
        ]);
        $ordered->push(Supplier::factory()->createQuietly([
            'latitude'  => null,
            'longitude' => null,
        ]));

        $supplierUserPreferred = SupplierUser::factory()->usingUser($companyUser->user)->createQuietly([
            'preferred' => true,
        ]);
        $ordered->prepend($supplierUserPreferred->supplier);

        $this->login($companyUser->user);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $ordered);
        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawSupplier, int $index) use ($ordered) {
            $supplier = $ordered->get($index);
            $this->assertSame($supplier->getRouteKey(), $rawSupplier['id']);
        });
    }

    /** @test */
    public function it_can_search_for_suppliers_by_text()
    {
        Supplier::factory()->count(2)->createQuietly(['name' => 'A regular supplier']);
        $suppliers = Supplier::factory()->count(3)->createQuietly(['name' => 'A special supplier']);
        $route     = URL::route($this->routeName);

        $this->login();
        $response = $this->getWithParameters($route, [RequestKeys::SEARCH_STRING => 'special']);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertSame($suppliers->count(), $response->json('meta.total'));

        $data               = Collection::make($response->json('data'))->pluck('id');
        $firstPageSuppliers = $suppliers->values()->take(count($data))->pluck(Supplier::routeKeyName());
        $this->assertEqualsCanonicalizing($data, $firstPageSuppliers);
    }

    /** @test
     * @throws Exception
     */
    public function it_can_order_for_suppliers_zip_code()
    {
        Supplier::factory()->count(2)->createQuietly(['zip_code' => 54321]);
        $suppliers = Supplier::factory()->count(3)->createQuietly(['zip_code' => $zipCode = '02345']);
        $route     = URL::route($this->routeName);
        $this->login();

        $response = $this->getWithParameters($route, [RequestKeys::ZIP_CODE => $zipCode]);

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(5, $response->json('meta.total'));

        $data = Collection::make($response->json('data'))->take($suppliers->count());

        $data->each(function(array $rawSupplier, int $index) use ($suppliers) {
            $supplier = $suppliers->get($index);
            $this->assertSame($supplier->zip_code, $rawSupplier['zip_code']);
        });
    }

    /** @test
     * @throws Exception
     */
    public function validated_parameters_are_present_in_pagination_links()
    {
        $this->login();

        $route    = URL::route($this->routeName);
        $response = $this->getWithParameters($route, [
            RequestKeys::SEARCH_STRING => 'search string',
            RequestKeys::ZIP_CODE      => '12345',
            'invalid'                  => 'invalid',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $parsedUrl = parse_url($response->json('links.first'));
        parse_str($parsedUrl['query'], $queryString);

        $this->assertArrayHasKey(RequestKeys::SEARCH_STRING, $queryString);
        $this->assertArrayHasKey(RequestKeys::ZIP_CODE, $queryString);
        $this->assertArrayNotHasKey('invalid', $queryString);
    }
}
