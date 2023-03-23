<?php

namespace Tests\Feature\Api\V3\Account\Supplier;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\SupplierController;
use App\Http\Resources\Api\V3\Account\Supplier\BaseResource;
use App\Models\Supplier;
use App\Models\SupplierUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
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

    private string $routeName = RouteNames::API_V3_ACCOUNT_SUPPLIER_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_a_preferred_supplier_first()
    {
        $user = User::factory()->create();
        SupplierUser::factory()->usingUser($user)->count(5)->createQuietly();
        $supplierPreferred = Supplier::factory()->createQuietly();
        SupplierUser::factory()->usingUser($user)->usingSupplier($supplierPreferred)->createQuietly([
            'preferred' => true,
        ]);

        $route = Url::route($this->routeName);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $data     = Collection::make($response->json('data'));
        $supplier = $data->first();
        $this->assertSame($supplier['id'], $supplierPreferred->getRouteKey());
    }

    /** @test */
    public function it_displays_a_list_of_user_related_suppliers()
    {
        $user                      = User::factory()->create();
        $supplierUsersNotPreferred = SupplierUser::factory()->usingUser($user)->count(5)->createQuietly();
        $supplierUserPreferred     = SupplierUser::factory()->usingUser($user)->createQuietly([
            'preferred' => true,
        ]);

        $suppliers = Collection::make([]);
        $suppliers->push($supplierUserPreferred->supplier->getRouteKey());
        $supplierUsersNotPreferred->each(fn(SupplierUser $supplierUser
        ) => $suppliers->push($supplierUser->supplier->getRouteKey()));

        $route = Url::route($this->routeName);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $suppliers);

        $data               = Collection::make($response->json('data'));
        $firstPageSuppliers = $suppliers->values()->take(count($data));

        $this->assertEqualsCanonicalizing($firstPageSuppliers->toArray(), $data->pluck('id')->toArray());
    }

    /** @test */
    public function it_displays_a_list_of_visible_suppliers()
    {
        $user             = User::factory()->create();
        $visibleSuppliers = SupplierUser::factory()->usingUser($user)->count(5)->createQuietly()->pluck('supplier');

        SupplierUser::factory()->usingUser($user)->count(3)->notVisible()->createQuietly();

        $route = Url::route($this->routeName);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $visibleSuppliers);

        $data               = Collection::make($response->json('data'));
        $firstPageSuppliers = $visibleSuppliers->values()->take(count($data))->pluck(Supplier::routeKeyName());

        $this->assertEqualsCanonicalizing($firstPageSuppliers->toArray(), $data->pluck('id')->toArray());
    }
}
