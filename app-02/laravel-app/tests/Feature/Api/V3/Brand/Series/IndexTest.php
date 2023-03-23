<?php

namespace Tests\Feature\Api\V3\Brand\Series;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Brand\SeriesController;
use App\Http\Requests\Api\V3\Brand\Series\IndexRequest;
use App\Http\Resources\Api\V3\Brand\Series\BaseResource;
use App\Models\Brand;
use App\Models\BrandDetailCounter;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see SeriesController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_BRAND_SERIES_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $brand = Brand::factory()->create();
        $route = URL::route($this->routeName, [RouteParameters::BRAND => $brand]);

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
    public function it_display_a_list_of_series_for_the_brand_ordered_alphabetically()
    {
        $brand  = Brand::factory()->published()->create();
        $series = Series::factory()->published()->usingBrand($brand)->count(20)->create();

        $route = URL::route($this->routeName, [RouteParameters::BRAND => $brand]);

        $this->login();
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $series);

        $data = Collection::make($response->json('data'));

        $firstPageSeries = $series->sortBy('name')->values()->take(count($data));

        $data->each(function(array $rawSeries, int $index) use ($firstPageSeries) {
            $seriesItem = $firstPageSeries->get($index);
            $this->assertSame($seriesItem->getRouteKey(), $rawSeries['id']);
        });
    }

    /** @test */
    public function it_can_search_for_series_name_by_text()
    {
        $brand = Brand::factory()->published()->create();
        Series::factory()->published()->usingBrand($brand)->count(2)->create(['name' => 'Regular name']);
        $series = Series::factory()->published()->usingBrand($brand)->count(3)->create(['name' => 'Special name']);

        $route = URL::route($this->routeName, [RouteParameters::BRAND => $brand]);

        $this->login();
        $response = $this->getWithParameters($route, [RequestKeys::SEARCH_STRING => 'special']);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $series);

        $data = Collection::make($response->json('data'));

        $firstPageSeries = $series->sortBy('name')->values()->take(count($data));

        $data->each(function(array $rawSeries, int $index) use ($firstPageSeries) {
            $seriesItem = $firstPageSeries->get($index);
            $this->assertSame($seriesItem->getRouteKey(), $rawSeries['id']);
        });
    }

    /** @test */
    public function it_does_not_show_inactive()
    {
        $brand = Brand::factory()->published()->create();
        Series::factory()->unpublished()->usingBrand($brand)->count(2)->create();
        Series::factory()->active()->usingBrand($brand)->count(3)->create();

        $route = URL::route($this->routeName, $brand);

        $this->login();
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(3, $response->json('meta.total'));
    }

    /** @test */
    public function it_saves_a_register_on_brand_detail_counter()
    {
        $user   = User::factory()->create();
        $brand  = Brand::factory()->published()->create();
        $route = URL::route($this->routeName, [RouteParameters::BRAND => $brand]);

        $this->login($user);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $this->assertDatabaseCount(BrandDetailCounter::tableName(), 1);

        $this->assertDatabaseHas(BrandDetailCounter::tableName(), [
            'brand_id' => $brand->getKey(),
            'user_id'  => $user->getKey(),
        ]);
    }
}
