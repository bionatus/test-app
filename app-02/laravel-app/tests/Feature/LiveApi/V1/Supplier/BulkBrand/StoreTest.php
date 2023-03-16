<?php

namespace Tests\Feature\LiveApi\V1\Supplier\BulkBrand;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\Supplier\BulkBrandController;
use App\Http\Requests\LiveApi\V1\Supplier\BulkBrand\StoreRequest;
use App\Http\Resources\LiveApi\V1\Supplier\BulkBrand\BaseResource;
use App\Models\Brand;
use App\Models\BrandSupplier;
use App\Models\Staff;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see BulkBrandController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_SUPPLIER_BULK_BRAND_STORE;

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

    /** @test */
    public function it_creates_brand_suppliers()
    {
        $staff = Staff::factory()->createQuietly();
        $store = $staff->supplier;
        Auth::shouldUse('live');
        $this->login($staff);

        $brands     = Brand::factory()->count(5)->create();
        $brandSlugs = $brands->pluck(Brand::routeKeyName())->toArray();
        $route      = URL::route($this->routeName);

        $response = $this->post($route, [RequestKeys::BRANDS => $brandSlugs]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->collectionSchema(BaseResource::jsonSchema()), $response);

        $brands->each(function($brand) use ($store) {
            $this->assertDatabaseHas(BrandSupplier::tableName(),
                ['brand_id' => $brand->getKey(), 'supplier_id' => $store->getKey()]);
        });
    }

    /** @test */
    public function it_syncs_brand_suppliers()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        Auth::shouldUse('live');
        $this->login($staff);

        $oldBrandStores = BrandSupplier::factory()->usingSupplier($supplier)->count(5)->create();
        $brands         = Brand::factory()->count(3)->create();
        $brandSlugs     = $brands->pluck(Brand::routeKeyName())->toArray();
        $route          = Url::route($this->routeName);

        $response = $this->post($route, [RequestKeys::BRANDS => $brandSlugs]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->collectionSchema(BaseResource::jsonSchema()), $response);

        $oldBrandStores->each(function($brandStore) {
            $this->assertDatabaseMissing(BrandSupplier::tableName(),
                ['brand_id' => $brandStore->brand_id, 'supplier_id' => $brandStore->store_id]);
        });

        $brands->each(function($brand) use ($supplier) {
            $this->assertDatabaseHas(BrandSupplier::tableName(),
                ['brand_id' => $brand->getKey(), 'supplier_id' => $supplier->getKey()]);
        });
    }
}
