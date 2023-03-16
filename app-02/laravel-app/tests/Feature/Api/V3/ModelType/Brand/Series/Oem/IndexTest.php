<?php

namespace Tests\Feature\Api\V3\ModelType\Brand\Series\Oem;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\ModelType\Brand\Series\OemController;
use App\Http\Requests\Api\V3\ModelType\Brand\Series\Oem\IndexRequest;
use App\Http\Resources\Api\V3\ModelType\Brand\Series\Oem\BaseResource;
use App\Models\Brand;
use App\Models\ModelType;
use App\Models\Oem;
use App\Models\Series;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see OemController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_MODEL_TYPE_BRAND_SERIES_OEM_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $modelType = ModelType::factory()->create();
        $series    = Series::factory()->create();
        $route     = URL::route($this->routeName, [
            RouteParameters::BRAND      => $series->brand,
            RouteParameters::SERIES     => $series,
            RouteParameters::MODEL_TYPE => $modelType,
        ]);

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

        $modelType = ModelType::factory()->create();
        $brand     = Brand::factory()->create();
        Series::factory()->usingBrand($brand)->create();
        $series = Series::factory()->create();

        $route = URL::route($this->routeName, [
            RouteParameters::BRAND      => $brand,
            RouteParameters::SERIES     => $series,
            RouteParameters::MODEL_TYPE => $modelType,
        ]);

        $this->login();

        $this->expectException(ModelNotFoundException::class);

        $this->get($route);
    }

    /** @test */
    public function it_display_a_list_of_oems_for_the_series_ordered_alphabetically()
    {
        $modelType    = ModelType::factory()->create();
        $series       = Series::factory()->create();
        $expectedOems = Collection::make([]);
        $expectedOems->add(Oem::factory()
            ->usingSeries($series)
            ->usingModelType($modelType)
            ->create(['model' => 'oem model b']));
        $expectedOems->add(Oem::factory()
            ->usingSeries($series)
            ->usingModelType($modelType)
            ->create(['model' => 'oem model c']));
        $expectedOems->prepend(Oem::factory()->usingSeries($series)->usingModelType($modelType)->create([
            'model'       => 'oem model a',
            'model_notes' => 'note c',
        ]));
        $expectedOems->prepend(Oem::factory()->usingSeries($series)->usingModelType($modelType)->create([
            'model'       => 'oem model a',
            'model_notes' => 'note b',
        ]));
        $expectedOems->prepend(Oem::factory()->usingSeries($series)->usingModelType($modelType)->create([
            'model'       => 'oem model a',
            'model_notes' => 'note a',
        ]));

        Oem::factory()->usingSeries($series)->usingModelType($modelType)->create([
            'status' => null,
            'model'  => 'oem model d',
        ]);

        $route = URL::route($this->routeName, [
            RouteParameters::BRAND      => $series->brand,
            RouteParameters::SERIES     => $series,
            RouteParameters::MODEL_TYPE => $modelType,
        ]);

        $this->login();
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $expectedOems);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawOem, int $index) use ($expectedOems) {
            $oem = $expectedOems->get($index);
            $this->assertSame((string) $oem->getRouteKey(), $rawOem['id']);
        });
    }

    /** @test */
    public function it_can_search_for_oems_model_by_text()
    {
        $modelType = ModelType::factory()->create();
        $series    = Series::factory()->create();
        Oem::factory()
            ->usingSeries($series)
            ->usingModelType($modelType)
            ->count(2)
            ->create(['model' => 'Regular model']);
        $oems = Oem::factory()
            ->usingSeries($series)
            ->usingModelType($modelType)
            ->count(3)
            ->create(['model' => 'Special model']);

        $route = URL::route($this->routeName, [
            RouteParameters::BRAND      => $series->brand,
            RouteParameters::SERIES     => $series,
            RouteParameters::MODEL_TYPE => $modelType,
        ]);

        $this->login();
        $response = $this->getWithParameters($route, [RequestKeys::SEARCH_STRING => 'special']);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $oems);

        $data = Collection::make($response->json('data'));

        $firstPageOems = $oems->sortBy('model')->values()->take(count($data));

        $data->each(function(array $rawOem, int $index) use ($firstPageOems) {
            $oem = $firstPageOems->get($index);
            $this->assertSame((string) $oem->getRouteKey(), $rawOem['id']);
        });
    }

    /** @test */
    public function it_does_not_show_not_live_oems()
    {
        $modelType = ModelType::factory()->create();
        $series    = Series::factory()->create();
        Oem::factory()->usingSeries($series)->usingModelType($modelType)->pending()->count(2)->create();
        Oem::factory()->usingSeries($series)->usingModelType($modelType)->live()->count(3)->create();

        $route = URL::route($this->routeName, [
            RouteParameters::BRAND      => $series->brand,
            RouteParameters::SERIES     => $series,
            RouteParameters::MODEL_TYPE => $modelType,
        ]);

        $this->login();
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(3, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_filter_oems_by_model_type()
    {
        $modelTypeName = 'Package Unit';
        $modelType     = ModelType::factory()->create(['name' => $modelTypeName]);
        $series        = Series::factory()->active()->create();
        Oem::factory()->usingSeries($series)->usingModelType($modelType)->live()->count(3)->create();
        Oem::factory()->usingSeries($series)->live()->count(2)->create();

        $route = URL::route($this->routeName, [
            RouteParameters::BRAND      => $series->brand,
            RouteParameters::SERIES     => $series,
            RouteParameters::MODEL_TYPE => $modelType,
        ]);

        $this->login();
        $response = $this->getWithParameters($route, [RequestKeys::MODEL_TYPE_ID => $modelType->id]);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals(3, $response->json('meta.total'));
    }
}
