<?php

namespace Tests\Feature\Api\V3\OrderSupplier;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\OrderSupplierController;
use App\Http\Resources\Api\V3\OrderSupplier\BaseResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Scopes\Alphabetically;
use App\Models\Supplier;
use App\Models\Supplier\Scopes\PublishedFavorite;
use App\Models\SupplierListView;
use App\Models\SupplierUser;
use App\Models\User;
use App\Types\CountryDataType;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see OrderSupplierController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ORDER_SUPPLIER_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $route = URL::route($this->routeName);

        $this->withoutExceptionHandling();
        $this->expectException(UnauthorizedHttpException::class);

        $this->get($route);
    }

    /** @test */
    public function it_displays_a_list_of_suppliers_ordered_by_published_favorite_and_distance_alphabetically()
    {
        $company     = Company::factory()->createQuietly([
            'country'   => CountryDataType::UNITED_STATES,
            'zip_code'  => '90210',
            'latitude'  => 0,
            'longitude' => 0,
        ]);
        $companyUser = CompanyUser::factory()->usingCompany($company)->create();
        $user        = $companyUser->user;

        $route = URL::route($this->routeName);

        $suppliersWithFavorites = Supplier::factory()
            ->has(SupplierUser::factory()->usingUser($user))
            ->createManyQuietly([
                [
                    'name'         => 'E',
                    'latitude'     => '1.3',
                    'longitude'    => '1.3',
                    'published_at' => '2022-06-18 14:20:00',
                ],
                [
                    'name'         => 'F',
                    'latitude'     => '1.4',
                    'longitude'    => '1.4',
                    'published_at' => '2022-06-16 14:20:00',
                ],
                [
                    'name'      => 'C',
                    'latitude'  => '1.4',
                    'longitude' => '1.4',
                ],
                [
                    'name'      => 'D',
                    'latitude'  => '1.4',
                    'longitude' => '1.4',
                ],
            ]);

        $suppliers = Supplier::factory()->createManyQuietly([
            [
                'latitude'  => '0',
                'longitude' => '0',
            ],
            [
                'name'      => 'B',
                'latitude'  => '1.2',
                'longitude' => '1.2',
            ],
        ]);

        $ordered = $suppliers->values();
        $ordered->push(Supplier::factory()->createQuietly([
            'latitude'  => null,
            'longitude' => null,
        ]));

        $allSuppliers = $suppliersWithFavorites->merge($ordered);

        $this->login($user);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $allSuppliers);

        $data = Collection::make($response->json('data'));
        $data->each(function(array $rawSupplier, int $index) use ($allSuppliers) {
            $supplier = $allSuppliers->get($index);
            $this->assertSame($supplier->getRouteKey(), $rawSupplier['id']);
        });
    }

    /** @test */
    public function it_displays_a_list_of_orders_suppliers_ordered_by_favorite_and_alphabetically()
    {
        $company     = Company::factory()->create(['country' => 'NON-US']);
        $companyUser = CompanyUser::factory()->usingCompany($company)->create();

        $route = URL::route($this->routeName);

        $user = $companyUser->user;

        Supplier::factory()->has(SupplierUser::factory()->usingUser($user))->count(5)->createQuietly()->sortBy(function(
            Supplier $supplier
        ) {
            return strtolower($supplier->name);
        });

        Supplier::factory()->count(5)->createQuietly()->sortBy(function(Supplier $supplier) {
            return strtolower($supplier->name);
        });
        $suppliers = Supplier::query()->scoped(new PublishedFavorite($user))->scoped(new Alphabetically())->get();

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
    public function it_displays_a_list_of_orders_suppliers_ordered_by_favorite_by_published_at_and_alphabetically()
    {
        $company     = Company::factory()->create(['country' => 'NON-US']);
        $companyUser = CompanyUser::factory()->usingCompany($company)->create();

        $route = URL::route($this->routeName);

        $user = $companyUser->user;

        Supplier::factory()->createQuietly(['published_at' => Carbon::now()]);
        Supplier::factory()->has(SupplierUser::factory()->usingUser($user))->count(5)->createQuietly();
        Supplier::factory()->count(5)->createQuietly();
        $supplierFavoritePublishedAt = Supplier::factory()->createQuietly(['published_at' => Carbon::now()]);

        SupplierUser::factory()->usingSupplier($supplierFavoritePublishedAt)->usingUser($user)->createQuietly();

        $suppliers = Supplier::query()->scoped(new PublishedFavorite($user))->scoped(new Alphabetically())->get();

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

        $this->assertEquals($supplierFavoritePublishedAt->getRouteKey(), $data->first()['id']);
    }

    /** @test */
    public function it_displays_a_list_of_suppliers_ordered_by_distance_alphabetically()
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
                'latitude'  => '0',
                'longitude' => '0',
            ],
            [
                'latitude'     => '1',
                'longitude'    => '1',
                'published_at' => '2022-06-18 18:00:00',
            ],
            [
                'name'      => 'A',
                'latitude'  => '1.2',
                'longitude' => '1.2',
            ],
            [
                'name'         => 'B',
                'latitude'     => '1.2',
                'longitude'    => '1.2',
                'published_at' => '2022-06-18 13:00:00',
            ],
        ]);

        $ordered = $suppliers->values();
        $ordered->push(Supplier::factory()->createQuietly([
            'latitude'  => null,
            'longitude' => null,
        ]));

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
    public function it_displays_a_list_of_suppliers_ordered_alphabetically_when_company_zip_code_is_outside_us()
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
    public function it_displays_a_list_of_suppliers_ordered_alphabetically_when_company_has_no_valid_coordinates()
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
    public function it_displays_a_list_of_suppliers_ordered_by_preferred_and_distance_to_the_user_location_and_alphabetically(
    )
    {
        $user = User::factory()->create();

        $location = '0,0';
        $route    = URL::route($this->routeName, [RequestKeys::LOCATION => $location]);

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

        $supplierUserPreferred = SupplierUser::factory()->usingUser($user)->createQuietly([
            'preferred' => true,
        ]);
        $ordered->prepend($supplierUserPreferred->supplier);

        $this->login($user);
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
    public function it_can_search_suppliers_by_name()
    {
        $user      = User::factory()->create();
        $suppliers = Supplier::factory()->count(3)->createQuietly(['name' => 'name special']);
        Supplier::factory()->count(10)->createQuietly();
        $searchString = 'spec';
        $route        = URL::route($this->routeName, [RequestKeys::SEARCH_STRING => $searchString]);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals($suppliers->count(), $response->json('meta.total'));

        $firstPageSuppliers = $suppliers->take($response->json('meta.per_page'));
        $data               = Collection::make($response->json('data'));
        $this->assertEqualsCanonicalizing($firstPageSuppliers->pluck('uuid'), $data->pluck('id'));
    }

    /** @test */
    public function it_can_search_suppliers_by_address()
    {
        $user      = User::factory()->create();
        $suppliers = Supplier::factory()->count(3)->createQuietly(['address' => 'special address']);
        Supplier::factory()->count(10)->createQuietly();
        $searchString = 'spec';
        $route        = URL::route($this->routeName, [RequestKeys::SEARCH_STRING => $searchString]);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $firstPageSuppliers = $suppliers->take($response->json('meta.per_page'));
        $data               = Collection::make($response->json('data'));
        $this->assertEqualsCanonicalizing($firstPageSuppliers->pluck('uuid'), $data->pluck('id'));
    }

    /** @test */
    public function it_can_search_suppliers_by_city()
    {
        $user      = User::factory()->create();
        $suppliers = Supplier::factory()->count(3)->createQuietly(['address' => 'special city']);
        Supplier::factory()->count(10)->createQuietly();
        $searchString = 'spec';
        $route        = URL::route($this->routeName, [RequestKeys::SEARCH_STRING => $searchString]);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $firstPageSuppliers = $suppliers->take($response->json('meta.per_page'));
        $data               = Collection::make($response->json('data'));
        $this->assertEqualsCanonicalizing($firstPageSuppliers->pluck('uuid'), $data->pluck('id'));
    }

    /** @test */
    public function it_can_search_suppliers_by_zip_code()
    {
        $user      = User::factory()->create();
        $suppliers = Supplier::factory()->count(3)->createQuietly(['zip_code' => '12345']);
        Supplier::factory()->count(10)->createQuietly();
        $searchString = '234';
        $route        = URL::route($this->routeName, [RequestKeys::SEARCH_STRING => $searchString]);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $firstPageSuppliers = $suppliers->take($response->json('meta.per_page'));
        $data               = Collection::make($response->json('data'));
        $this->assertEqualsCanonicalizing($firstPageSuppliers->pluck('uuid'), $data->pluck('id'));
    }

    /** @test
     * @dataProvider pageProvider
     *
     * @param int  $page
     * @param bool $shouldCreateDatabaseRecord
     */
    public function it_records_the_supplier_list_view_by_the_user_depending_of_page(
        int $page,
        bool $shouldCreateDatabaseRecord
    ) {
        $user = User::factory()->create();
        Supplier::factory()->count(10)->createQuietly();
        $route = URL::route($this->routeName, ['page' => $page]);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        if ($shouldCreateDatabaseRecord) {
            $this->assertDatabaseHas(SupplierListView::tableName(), [
                'user_id' => $user->getKey(),
            ]);
        }

        $this->assertDatabaseCount(SupplierListView::tableName(), $shouldCreateDatabaseRecord);
    }

    public function pageProvider(): array
    {
        return [
            [1, true],
            [2, false],
            [100, false],
        ];
    }
}
