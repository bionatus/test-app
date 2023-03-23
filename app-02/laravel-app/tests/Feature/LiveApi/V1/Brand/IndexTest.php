<?php

namespace Tests\Feature\LiveApi\V1\Brand;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\BrandController;
use App\Http\Requests\LiveApi\V1\Brand\IndexRequest;
use App\Http\Resources\LiveApi\V1\Brand\BaseResource;
use App\Models\Brand;
use App\Models\Staff;
use Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see BrandController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_BRAND_INDEX;

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
    public function it_displays_a_list_of_brands()
    {
        $brands = Brand::factory()->count(20)->create();
        $route  = URL::route($this->routeName);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data           = Collection::make($response->json('data'));
        $expectedBrands = $brands->sortBy('name')->values();

        $data->each(function(array $rawBrand, int $index) use ($expectedBrands) {
            $brand = $expectedBrands->get($index);
            $this->assertSame($brand->getRouteKey(), $rawBrand['id']);
        });
    }

    /** @test */
    public function it_can_search_for_brands_name_by_text()
    {
        Brand::factory()->create(['name' => 'Brand Lorem', 'published_at' => Carbon::now()]);

        $brands = Collection::make([
            Brand::factory()->create(['name' => 'Special Brand', 'published_at' => Carbon::now()]),
            Brand::factory()->create(['name' => 'New Brand Special Lorem', 'published_at' => Carbon::now()]),
        ]);
        $route  = URL::route($this->routeName);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
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
}
