<?php

namespace Tests\Feature\LiveApi\V1\Brand\Series\Oem;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Brand\Series\OemController;
use App\Http\Requests\LiveApi\V1\Brand\Series\Oem\IndexRequest;
use App\Http\Resources\LiveApi\V1\Brand\Series\Oem\BaseResource;
use App\Models\Brand;
use App\Models\Oem;
use App\Models\Series;
use App\Models\Staff;
use Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see OemController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_BRAND_SERIES_OEM_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $series = Series::factory()->create();
        $route  = URL::route($this->routeName,
            [RouteParameters::BRAND => $series->brand, RouteParameters::SERIES => $series]);

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
    public function it_throws_error_trying_to_access_an_invalid_brand_series_combination()
    {
        $this->withoutExceptionHandling();

        $brand = Brand::factory()->create();
        Series::factory()->usingBrand($brand)->create();
        $series = Series::factory()->create();

        $route = URL::route($this->routeName, [
            RouteParameters::BRAND  => $brand,
            RouteParameters::SERIES => $series,
        ]);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());

        $this->expectException(ModelNotFoundException::class);

        $this->get($route);
    }

    /** @test */
    public function it_display_a_list_of_oems_for_the_series_sorted_by_model_and_model_notes_alphabetically()
    {
        $series       = Series::factory()->create();
        $expectedOems = Collection::make([]);
        $expectedOems->add(Oem::factory()->usingSeries($series)->create(['model' => 'oem model b']));
        $expectedOems->add(Oem::factory()->usingSeries($series)->create(['model' => 'oem model c']));
        $expectedOems->prepend(Oem::factory()->usingSeries($series)->create([
            'model'       => 'oem model a',
            'model_notes' => 'note c',
        ]));
        $expectedOems->prepend(Oem::factory()->usingSeries($series)->create([
            'model'       => 'oem model a',
            'model_notes' => 'note b',
        ]));
        $expectedOems->prepend(Oem::factory()->usingSeries($series)->create([
            'model'       => 'oem model a',
            'model_notes' => 'note a',
        ]));

        Oem::factory()->usingSeries($series)->createQuietly(['status' => null, 'model' => 'oem model d']);

        $route = URL::route($this->routeName, [
            RouteParameters::BRAND  => $series->brand,
            RouteParameters::SERIES => $series,
        ]);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $expectedOems);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawOem, int $index) use ($expectedOems) {
            $oem = $expectedOems->get($index);
            $this->assertSame($oem->getRouteKey(), $rawOem['id']);
        });
    }

    /** @test */
    public function it_does_not_show_not_live_oems()
    {
        $series = Series::factory()->create();
        Oem::factory()->usingSeries($series)->pending()->count(2)->create();
        Oem::factory()->usingSeries($series)->live()->count(3)->create();

        $route = URL::route($this->routeName, [
            RouteParameters::BRAND  => $series->brand,
            RouteParameters::SERIES => $series,
        ]);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(3, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_search_for_model_name_by_text()
    {
        $series = Series::factory()->create();

        $route = URL::route($this->routeName, [
            RouteParameters::BRAND  => $series->brand,
            RouteParameters::SERIES => $series,
        ]);

        Oem::factory()->usingSeries($series)->live()->create(['model' => 'Another Model']);

        $oems = Collection::make([
            Oem::factory()->usingSeries($series)->live()->create(['model' => 'Test Name ObjectiveOne Model']),
            Oem::factory()->usingSeries($series)->live()->create(['model' => 'Lorem Ipsum objective']),
        ]);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $response = $this->getWithParameters($route, [RequestKeys::SEARCH_STRING => 'objective']);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $oems);

        $data = Collection::make($response->json('data'));

        $firstPageOems = $oems->sortBy('model')->values()->take(count($data));

        $data->each(function(array $rawBrand, int $index) use ($firstPageOems) {
            $brand = $firstPageOems->get($index);
            $this->assertSame($brand->getRouteKey(), $rawBrand['id']);
        });
    }
}
