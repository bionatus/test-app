<?php

namespace Tests\Feature\Api\V3\ModelType\Brand;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\ModelType\BrandController;
use App\Http\Requests\Api\V3\ModelType\Brand\IndexRequest;
use App\Http\Resources\Api\V3\ModelType\Brand\BaseResource;
use App\Models\Brand;
use App\Models\ModelType;
use App\Models\Oem;
use App\Models\Series;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see BrandController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_MODEL_TYPE_BRAND_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $modelType = ModelType::factory()->create();
        $route     = URL::route($this->routeName, [RouteParameters::MODEL_TYPE => $modelType]);
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
    public function it_display_a_list_of_brands_published_ordered_alphabetically()
    {
        $modelType = ModelType::factory()->create();

        $brands = Brand::factory()->count(20)->create(['published_at' => Carbon::now()])->each(function($brand) use (
            $modelType
        ) {
            $series = Series::factory()->usingBrand($brand)->create();
            Oem::factory()->usingSeries($series)->usingModelType($modelType)->create();
        });

        $route = URL::route($this->routeName, [RouteParameters::MODEL_TYPE => $modelType]);

        $this->login();
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $brands);

        $data = Collection::make($response->json('data'));

        $firstPageBrands = $brands->sortBy('name')->values()->take(count($data));

        $data->each(function(array $rawBrand, int $index) use ($firstPageBrands) {
            $brand = $firstPageBrands->get($index);
            $this->assertSame($brand->getRouteKey(), $rawBrand['id']);
        });
    }

    /** @test */
    public function it_can_search_for_brands_name_by_text()
    {
        Brand::factory()->create(['name' => 'Brand Lorem', 'published_at' => Carbon::now()]);

        $brands    = Collection::make([
            Brand::factory()->create(['name' => 'Special Brand', 'published_at' => Carbon::now()]),
            Brand::factory()->create(['name' => 'New Brand Special Lorem', 'published_at' => Carbon::now()]),
        ]);
        $modelType = ModelType::factory()->create();
        $brands->each(function($brand) use ($modelType) {
            $series = Series::factory()->usingBrand($brand)->create();
            Oem::factory()->usingSeries($series)->usingModelType($modelType)->create();
        });
        $route = URL::route($this->routeName, [RouteParameters::MODEL_TYPE => $modelType]);

        $this->login();
        $response = $this->getWithParameters($route, [RequestKeys::SEARCH_STRING => 'Special']);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $brands);

        $data = Collection::make($response->json('data'));

        $firstPageBrands = $brands->sortBy('name')->values()->take(count($data));

        $data->each(function(array $rawBrand, int $index) use ($firstPageBrands) {
            $brand = $firstPageBrands->get($index);
            $this->assertSame($brand->getRouteKey(), $rawBrand['id']);
        });
    }

    /** @test */
    public function it_can_filter_brands_by_oem_type()
    {
        $modelTypeName = 'Package Unit';
        $modelType     = ModelType::factory()->create(['name' => $modelTypeName]);
        $brand         = Brand::factory()->create(['published_at' => Carbon::now()]);
        $series        = Series::factory()->usingBrand($brand)->create();
        Oem::factory()->usingSeries($series)->usingModelType($modelType)->count(2)->create();

        Brand::factory()->count(2)->create(['published_at' => Carbon::now()]);

        $route = URL::route($this->routeName, [RouteParameters::MODEL_TYPE => $modelType]);

        $this->login();
        $response = $this->getWithParameters($route, [RequestKeys::MODEL_TYPE_ID => $modelType->id]);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_does_not_show_not_published()
    {
        $modelType = ModelType::factory()->create();
        Brand::factory()->count(2)->create();
        Brand::factory()->count(3)->create(['published_at' => Carbon::now()])->each(function($brand) use ($modelType) {
            $series = Series::factory()->usingBrand($brand)->create();
            Oem::factory()->usingSeries($series)->usingModelType($modelType)->create();
        });

        $route = URL::route($this->routeName, [RouteParameters::MODEL_TYPE => $modelType]);

        $this->login();
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(3, $response->json('meta.total'));
    }
}
