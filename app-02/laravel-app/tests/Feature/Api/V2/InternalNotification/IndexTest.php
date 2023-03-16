<?php

namespace Tests\Feature\Api\V2\InternalNotification;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V2\InternalNotificationController;
use App\Http\Resources\Api\V2\InternalNotification\BaseResource;
use App\Models\InternalNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see InternalNotificationController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_INTERNAL_NOTIFICATION_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_a_list_of_notifications()
    {
        $user          = User::factory()->create();
        $notifications = InternalNotification::factory()->usingUser($user)->count(20)->create();
        $route         = URL::route($this->routeName);

        $this->login($user);
        $response               = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $notifications);

        $data                   = Collection::make($response->json('data'));
        $firstPageNotifications = $notifications->sortByDesc(InternalNotification::CREATED_AT)
            ->sortByDesc(InternalNotification::keyName())
            ->values()
            ->take(count($data));

        $data->each(function(array $rawNotification, int $index) use ($firstPageNotifications) {
            $notification = $firstPageNotifications->get($index);
            $this->assertEquals($notification->getRouteKey(), $rawNotification['id']);
        });
    }
}
