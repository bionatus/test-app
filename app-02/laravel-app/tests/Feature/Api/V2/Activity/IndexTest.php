<?php

namespace Tests\Feature\Api\V2\Activity;

use App\Constants\RouteNames;
use App\Http\Resources\Api\V2\Activity\BaseResource;
use App\Models\Activity;
use App\Models\RelatedActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see ActivityController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_ACTIVITY_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_display_an_activity_list_newest_first()
    {
        $user         = User::factory()->create();
        $activityList = Activity::factory()->usingCauser($user)->count(100)->create();

        $this->login($user);
        $response = $this->get(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $activityList);

        $data                = Collection::make($response->json('data'));
        $firstPageActivities = $activityList->reverse()->values()->take(count($data));

        $data->each(function(array $rawActivity, int $index) use ($firstPageActivities) {
            $activity = $firstPageActivities->get($index);
            $this->assertSame($activity->getRouteKey(), $rawActivity['id']);
        });
    }

    /** @test */
    public function it_displays_other_users_related_activities()
    {
        $user = User::factory()->create();
        Activity::factory()->count(100)->create();
        $activities        = Activity::factory()->usingCauser($user)->count(5)->create();
        $relatedActivities = RelatedActivity::factory()->usingUser($user)->count(5)->create();
        $activityList      = $activities->concat($relatedActivities->pluck('activity'))->sortBy([
            ['created_at', 'asc'],
            ['id', 'asc'],
        ]);

        $this->login($user);
        $response = $this->get(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $activityList);

        $data                = Collection::make($response->json('data'));
        $firstPageActivities = $activityList->reverse()->values()->take(count($data));

        $data->each(function(array $rawActivity, int $index) use ($firstPageActivities) {
            $activity = $firstPageActivities->get($index);
            $this->assertSame($activity->getRouteKey(), $rawActivity['id']);
        });
    }

    /** @test */
    public function it_displays_only_the_default_log_name()
    {
        $user = User::factory()->create();
        Activity::factory()->usingCauser($user)->count(5)->create();
        Activity::factory()->usingCauser($user)->count(3)->create(['log_name' => 'other']);

        $this->login($user);
        $response = $this->get(URL::route($this->routeName));
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->assertCount(5, $data);
    }
}
