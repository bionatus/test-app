<?php

namespace Tests\Feature\Api\V3\InternalNotification;

use App\AppNotification;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\InternalNotificationController;
use App\Http\Requests\Api\V3\InternalNotification\IndexRequest;
use App\Http\Resources\Api\V3\InternalNotification\BaseResource;
use App\Models\InternalNotification;
use App\Models\User;
use App\Notifications\UnreadNotificationCountUpdatedNotification as UnreadNotificationCount;
use Database\Factories\AppNotificationFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use ReflectionProperty;
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

    private string $routeName = RouteNames::API_V3_INTERNAL_NOTIFICATION_INDEX;

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
    public function it_displays_a_list_of_notifications()
    {
        $user          = User::factory()->create();
        $notifications = InternalNotification::factory()->usingUser($user)->count(20)->create();
        $route         = URL::route($this->routeName);

        $this->login($user);
        $response               = $this->get($route);
        $data                   = Collection::make($response->json('data'));
        $firstPageNotifications = $notifications->sortByDesc(InternalNotification::CREATED_AT)
            ->sortByDesc(InternalNotification::keyName())
            ->values()
            ->take(count($data));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $notifications);

        $data->each(function(array $rawNotification, int $index) use ($firstPageNotifications) {
            $notification = $firstPageNotifications->get($index);
            $this->assertEquals($notification->getRouteKey(), $rawNotification['id']);
        });
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_mark_all_unread_internal_notifications_for_the_user_as_read(?bool $read, bool $expected)
    {
        Carbon::setTestNow('2022-12-05 10:00:00');

        $user = User::factory()->create();
        InternalNotification::factory()->usingUser($user)->read()->count(4)->create();
        InternalNotification::factory()->usingUser($user)->count(3)->create();
        InternalNotification::factory()->count(3)->create();

        $routeWithParameter = URL::route($this->routeName, [RequestKeys::READ => $read]);

        $route = !is_null($read) ? $routeWithParameter : URL::route($this->routeName);

        $this->login($user);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);

        if ($expected) {
            $this->assertDatabaseMissing(InternalNotification::tableName(), [
                'read_at' => null,
                'user_id' => $user->getKey(),
            ]);
        }
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_mark_all_unread_app_notifications_for_the_user_as_read(?bool $read, bool $expected)
    {
        $user = User::factory()->create();
        (new AppNotificationFactory())->usingUser($user)
            ->read()
            ->count(10)
            ->create()
            ->pluck((new AppNotification())->getRouteKeyName());
        (new AppNotificationFactory())->usingUser($user)
            ->count(5)
            ->create()
            ->pluck((new AppNotification())->getRouteKeyName());

        $routeWithParameter = URL::route($this->routeName, [RequestKeys::READ => $read]);

        $route = !is_null($read) ? $routeWithParameter : URL::route($this->routeName);

        $this->login($user);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);

        if ($expected) {
            $this->assertDatabaseMissing((new AppNotification())->getTable(), [
                'read'    => null,
                'user_id' => $user->getKey(),
            ]);
        }
    }

    /** @test
     * @dataProvider dataProvider
     * @throws \Exception
     */
    public function it_sends_an_unread_notification_count_update_notification_with_value_0_if_requirements_are_met(
        ?bool $read,
        bool $expected
    ) {
        Notification::fake();

        InternalNotification::flushEventListeners();

        $user = User::factory()->create();
        InternalNotification::factory()->usingUser($user)->read()->count(10)->create();
        InternalNotification::factory()->usingUser($user)->count(5)->create();

        $routeWithParameter = URL::route($this->routeName, [RequestKeys::READ => $read]);

        $route = !is_null($read) ? $routeWithParameter : URL::route($this->routeName);

        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);

        if ($expected) {
            Notification::assertSentTo([$user], UnreadNotificationCount::class,
                function(UnreadNotificationCount $notification) {
                    $property = new ReflectionProperty(UnreadNotificationCount::class, 'unreadNotificationsCount');
                    $property->setAccessible(true);

                    return 0 == $property->getValue($notification);
                });
        }
    }

    public function dataProvider(): array
    {
        return [
            // read, expected
            [true, true],
            [false, false],
            [null, true],
        ];
    }
}
