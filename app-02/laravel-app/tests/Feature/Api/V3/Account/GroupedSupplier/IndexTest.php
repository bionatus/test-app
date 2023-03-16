<?php

namespace Tests\Feature\Api\V3\Account\GroupedSupplier;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\GroupedSupplierController;
use App\Http\Resources\Api\V3\Account\Supplier\GroupedResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Types\CountryDataType;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see GroupedSupplierController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ACCOUNT_GROUPED_SUPPLIER_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_a_list_of_suppliers_sorted_by_preferred_then_favorite_then_alphabetically_when_company_has_no_valid_coordinates(
    )
    {
        $company     = Company::factory()->create(['country' => 'US']);
        $companyUser = CompanyUser::factory()->usingCompany($company)->create();

        $route = URL::route($this->routeName);

        $suppliers         = Supplier::factory()->onTheNetwork()->count(20)->sequence(fn(Sequence $sequence) => [
            'name' => Str::lower(Str::random(10)),
        ])->createQuietly()->sortBy(function(Supplier $supplier) {
            return strtolower($supplier->name);
        });
        $preferredSupplier = Supplier::factory()->onTheNetwork()->createQuietly([
            'name' => Str::lower(Str::random(10)),
        ]);
        SupplierUser::factory()->usingUser($companyUser->user)->usingSupplier($preferredSupplier)->create([
            'preferred' => true,
        ]);
        $favoriteSuppliers = Supplier::factory()->onTheNetwork()->count(5)->sequence(fn(Sequence $sequence) => [
            'name' => Str::lower(Str::random(10)),
        ])->createQuietly()->sortBy(function(Supplier $supplier) {
            return strtolower($supplier->name);
        });
        $favoriteSuppliers->each(function(Supplier $supplier) use ($companyUser) {
            SupplierUser::factory()->usingUser($companyUser->user)->usingSupplier($supplier)->create();
        });
        $orderedSuppliers = $favoriteSuppliers->concat($suppliers)->prepend($preferredSupplier);

        $this->login($companyUser->user);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(GroupedResource::jsonSchema(), true), $response);

        $this->assertCount($response->json('meta.total'), $orderedSuppliers);

        $data               = Collection::make($response->json('data'));
        $firstPageSuppliers = $orderedSuppliers->values()->take(count($data));

        $data->each(function(array $rawSupplier, int $index) use ($firstPageSuppliers) {
            $supplier = $firstPageSuppliers->get($index);
            $this->assertSame($supplier->getRouteKey(), $rawSupplier['id']);
        });
    }

    /** @test */
    public function it_displays_a_list_of_suppliers_filtered_by_on_the_network()
    {
        $company     = Company::factory()->create(['country' => 'US']);
        $companyUser = CompanyUser::factory()->usingCompany($company)->create();

        $route = URL::route($this->routeName);

        $suppliersNotPublished = Supplier::factory()->verified()->unpublished()->createQuietly();
        $suppliersNotVerified  = Supplier::factory()->unverified()->published()->createQuietly();
        $suppliersPublished    = Supplier::factory()->onTheNetwork()->count(3)->sequence(fn(Sequence $sequence) => [
            'name' => Str::lower(Str::random(10)),
        ])->createQuietly()->sortBy(function(Supplier $supplier) {
            return strtolower($supplier->name);
        });

        $this->login($companyUser->user);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(GroupedResource::jsonSchema(), true), $response);

        $this->assertCount($response->json('meta.total'), $suppliersPublished);

        $data               = Collection::make($response->json('data'));
        $firstPageSuppliers = $suppliersPublished->values()->take(count($data));

        $data->each(function(array $rawSupplier, int $index) use ($firstPageSuppliers) {
            $supplier = $firstPageSuppliers->get($index);
            $this->assertSame($supplier->getRouteKey(), $rawSupplier['id']);
        });

        $this->assertNotContains($suppliersNotPublished->getRouteKey(), $data->pluck('id'));
        $this->assertNotContains($suppliersNotVerified->getRouteKey(), $data->pluck('id'));
    }

    /** @test */
    public function it_display_a_list_of_suppliers_ordered_by_preferred_then_favorite_then_distance_then_alphabetically_when_company_zip_code_is_in_us(
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

        $suppliers = Supplier::factory()->onTheNetwork()->createManyQuietly([
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
                'name'      => Str::lower(Str::random(10)),
                'latitude'  => '0',
                'longitude' => '0',
            ],
            [
                'name'      => Str::lower(Str::random(10)),
                'latitude'  => '1',
                'longitude' => '1',
            ],
        ])->sortBy([
            fn($a, $b) => $a->latitude <=> $b->latitude,
            fn($a, $b) => strtolower($a->name) <=> strtolower($b->name),
        ]);
        $suppliers->push(Supplier::factory()->onTheNetwork()->createQuietly([
            'name'      => Str::lower(Str::random(10)),
            'latitude'  => null,
            'longitude' => null,
        ]));
        $preferredSupplier = Supplier::factory()->onTheNetwork()->createQuietly([
            'name' => Str::lower(Str::random(10)),
        ]);
        SupplierUser::factory()->usingUser($companyUser->user)->usingSupplier($preferredSupplier)->create([
            'preferred' => true,
        ]);
        $favoriteSuppliers = Supplier::factory()->onTheNetwork()->count(5)->sequence(fn(Sequence $sequence) => [
            'name' => Str::lower(Str::random(10)),
        ])->createQuietly()->sortBy(function(Supplier $supplier) {
            return strtolower($supplier->name);
        });
        $favoriteSuppliers->each(function(Supplier $supplier) use ($companyUser) {
            SupplierUser::factory()->usingUser($companyUser->user)->usingSupplier($supplier)->create();
        });
        $orderedSuppliers = $favoriteSuppliers->concat($suppliers)->prepend($preferredSupplier);

        $this->login($companyUser->user);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(GroupedResource::jsonSchema(), true), $response);

        $this->assertCount($response->json('meta.total'), $orderedSuppliers);

        $data               = Collection::make($response->json('data'));
        $firstPageSuppliers = $orderedSuppliers->values()->take(count($data));

        $data->each(function(array $rawSupplier, int $index) use ($firstPageSuppliers) {
            $supplier = $firstPageSuppliers->get($index);
            $this->assertSame($supplier->getRouteKey(), $rawSupplier['id']);
        });
    }
}
