<?php

namespace Tests\Feature\LiveApi\V1\Brand\Series;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Brand\SeriesController;
use App\Http\Requests\LiveApi\V1\Brand\Series\IndexRequest;
use App\Http\Resources\LiveApi\V1\Brand\Series\BaseResource;
use App\Models\Brand;
use App\Models\BrandDetailCounter;
use App\Models\Series;
use App\Models\Staff;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see SeriesController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_BRAND_SERIES_INDEX;

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

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $series);

        $data = Collection::make($response->json('data'));

        $firstPageSeries = $series->sortBy('name')->values()->take(count($data));

        $data->each(function(array $rawOem, int $index) use ($firstPageSeries) {
            $seriesItem = $firstPageSeries->get($index);
            $this->assertSame($seriesItem->getRouteKey(), $rawOem['id']);
        });
    }

    /** @test */
    public function it_can_search_for_series_name_by_text()
    {
        $brand = Brand::factory()->published()->create();
        Series::factory()->published()->usingBrand($brand)->count(2)->create(['name' => 'Regular name']);
        $series = Series::factory()->published()->usingBrand($brand)->count(3)->create(['name' => 'Special name']);

        $route = URL::route($this->routeName, [RouteParameters::BRAND => $brand]);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $response = $this->getWithParameters($route, [RequestKeys::SEARCH_STRING => 'special']);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $series);

        $data = Collection::make($response->json('data'));

        $firstPageSeries = $series->sortBy('name')->values()->take(count($data));

        $data->each(function(array $rawOem, int $index) use ($firstPageSeries) {
            $seriesItem = $firstPageSeries->get($index);
            $this->assertSame($seriesItem->getRouteKey(), $rawOem['id']);
        });
    }

    /** @test */
    public function it_does_not_show_inactive()
    {
        $brand = Brand::factory()->published()->create();
        Series::factory()->unpublished()->usingBrand($brand)->count(2)->create();
        Series::factory()->active()->usingBrand($brand)->count(3)->create();

        $route = URL::route($this->routeName, $brand);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(3, $response->json('meta.total'));
    }

    /** @test */
    public function it_saves_a_register_on_brand_detail_counter()
    {
        $staff = Staff::factory()->createQuietly();
        $brand  = Brand::factory()->published()->create();
        $route = URL::route($this->routeName, [RouteParameters::BRAND => $brand]);

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $this->assertDatabaseCount(BrandDetailCounter::tableName(), 1);

        $this->assertDatabaseHas(BrandDetailCounter::tableName(), [
            'brand_id' => $brand->getKey(),
            'staff_id' => $staff->getKey(),
        ]);
    }
}
