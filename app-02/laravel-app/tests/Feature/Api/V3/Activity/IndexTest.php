<?php

namespace Tests\Feature\Api\V3\Activity;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\ActivityController;
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

    private string $routeName = RouteNames::API_V3_ACTIVITY_INDEX;

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
        Activity::factory()->usingCauser($user)->count(10)->create(['log_name' => 'other_log']);

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

    /** @test
     * @dataProvider typesProvider
     */
    public function it_filters_other_users_related_activities($type, $quantity, $otherTypes)
    {
        $user          = User::factory()->create();
        $otherQuantity = 7;
        $activity      = Activity::factory()->usingCauser($user)->count($quantity);
        if (null === $type) {
            $activity->create();
            $quantity += $otherQuantity;
        } else {
            $activity->create(['log_name' => $type]);
        }
        Activity::factory()->usingCauser($user)->count($otherQuantity)->create(['log_name' => $otherTypes]);

        $this->login($user);

        $response = $this->get(URL::route($this->routeName, ['log_name' => $type]));

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data->count(), $quantity);
    }

    public function typesProvider()
    {
        return [
            [Activity::TYPE_FORUM, 3, Activity::TYPE_PROFILE],
            [Activity::TYPE_ORDER, 5, Activity::TYPE_PROFILE],
            [Activity::TYPE_PROFILE, 10, Activity::TYPE_FORUM],
            [null, 5, Activity::TYPE_FORUM],
        ];
    }
}
