<?php

namespace Tests\Feature\Api\V3\AppVersion\Confirm;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\AppVersion\ConfirmController;
use App\Http\Requests\Api\V3\AppVersion\Confirm\InvokeRequest;
use App\Models\AppVersion;
use App\Models\Flag;
use App\Models\VideoElapsedTime;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see ConfirmController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_APP_VERSION_CONFIRM;

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
    public function it_confirms_the_app_version_and_stores_the_seconds_viewed_of_the_video()
    {
        $user = User::factory()->create();
        AppVersion::factory()->create(['current' => $current = '1.1.1']);

        $this->login($user);
        $response = $this->post(URL::route($this->routeName), [RequestKeys::SECONDS => $seconds = 5]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas(Flag::tableName(), [
            'name'           => "app-version-confirm-$current",
            'flaggable_type' => Relation::getAliasByModel(User::class),
            'flaggable_id'   => $user->getKey(),
        ]);
        $this->assertDatabaseHas(VideoElapsedTime::tableName(), [
            'user_id' => $user->getKey(),
            'version' => $current,
            'seconds' => $seconds,
        ]);
    }

    /** @test */
    public function it_updates_the_existing_record_if_the_user_and_version_is_the_same()
    {
        $user = User::factory()->create();
        AppVersion::factory()->create(['current' => $current = '1.1.1']);
        VideoElapsedTime::factory()->usingUser($user)->create(['version' => $current]);

        $this->login($user);
        $response = $this->post(URL::route($this->routeName), [RequestKeys::SECONDS => $seconds = 5]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseCount(VideoElapsedTime::tableName(), 1);
        $this->assertDatabaseHas(VideoElapsedTime::tableName(), [
            'user_id' => $user->getKey(),
            'version' => $current,
            'seconds' => $seconds,
        ]);
    }

    /** @test */
    public function it_updates_the_user_existing_record()
    {
        AppVersion::factory()->create(['current' => $current = '1.1.1']);
        VideoElapsedTime::factory()->count(2)->create(['version' => $current]);

        $user = User::factory()->create();
        VideoElapsedTime::factory()->usingUser($user)->create(['version' => $current]);

        $this->login($user);
        $response = $this->post(URL::route($this->routeName), [RequestKeys::SECONDS => $seconds = 5]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseCount(VideoElapsedTime::tableName(), 3);
        $this->assertDatabaseHas(VideoElapsedTime::tableName(), [
            'user_id' => $user->getKey(),
            'version' => $current,
            'seconds' => $seconds,
        ]);
    }
}
