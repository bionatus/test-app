<?php

namespace Tests\Feature\Api\V3\Account\BulkFavoriteSeries;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\BulkFavoriteSeriesController;
use App\Http\Requests\Api\V3\Account\BulkFavoriteSeries\InvokeRequest;
use App\Http\Resources\Api\V3\Account\BulkFavoriteSeries\BaseResource;
use App\Models\Series;
use App\Models\SeriesUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see BulkFavoriteSeriesController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ACCOUNT_BULK_FAVORITE_SERIES_STORE;

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
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_store_the_provided_series()
    {
        $user   = User::factory()->create();
        $series = Series::factory()->count(3)->create();

        $this->login($user);

        $route = URL::route($this->routeName);

        $response = $this->post($route, [
            RequestKeys::SERIES => $series->pluck(Series::routeKeyName())->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $series->each(function(Series $series) use ($user) {
            $this->assertDatabaseHas(SeriesUser::tableName(), [
                'user_id'   => $user->getKey(),
                'series_id' => $series->getKey(),
            ]);
        });

        $data = Collection::make($response->json('data'));
        $this->assertCount($series->count(), $data);

        $this->assertEqualsCanonicalizing($series->pluck(Series::keyName()), $data->pluck('id'));
    }

    /** @test */
    public function it_overwrite_the_existing_related_series_with_the_new_ones()
    {
        $user       = User::factory()->create();
        $seriesUser = SeriesUser::factory()->usingUser($user)->count(5)->create();
        $series     = Series::factory()->count(3)->create();

        $this->login($user);

        $route = URL::route($this->routeName);

        $response = $this->post($route, [
            RequestKeys::SERIES => $series->pluck(Series::routeKeyName())->toArray(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $series->each(function(Series $series) use ($user) {
            $this->assertDatabaseHas(SeriesUser::tableName(), [
                'user_id'   => $user->getKey(),
                'series_id' => $series->getKey(),
            ]);
        });

        $seriesUser->each(function(SeriesUser $seriesUser) {
            $this->assertModelMissing($seriesUser);
        });

        $this->assertDatabaseCount(SeriesUser::tableName(), $series->count());

        $data = Collection::make($response->json('data'));
        $this->assertCount($series->count(), $data);

        $this->assertEqualsCanonicalizing($series->pluck(Series::keyName()), $data->pluck('id'));
    }

    /** @test */
    public function it_removes_all_related_series_if_an_empty_array_is_sent()
    {
        $user       = User::factory()->create();
        $seriesUser = SeriesUser::factory()->usingUser($user)->count(5)->create();

        $this->login($user);

        $route = URL::route($this->routeName);

        $response = $this->post($route, [
            RequestKeys::SERIES => null,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $seriesUser->each(function(SeriesUser $seriesUser) {
            $this->assertModelMissing($seriesUser);
        });

        $this->assertDatabaseMissing(SeriesUser::tableName(), ['user_id' => $user->getKey()]);

        $data = Collection::make($response->json('data'));
        $this->assertCount(0, $data);
    }
}
