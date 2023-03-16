<?php

namespace Tests\Feature\Api\V3\InternalNotification\MarkAsRead;

use App\AppNotification;
use App\Constants\RouteNames;
use App\Models\InternalNotification;
use App\Models\User;
use App\Notifications\UnreadNotificationCountUpdatedNotification as UnreadNotificationCount;
use Database\Factories\AppNotificationFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use JMac\Testing\Traits\AdditionalAssertions;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see MarkAsReadController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_INTERNAL_NOTIFICATION_MARK_AS_READ;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->patch(URL::route($this->routeName));
    }

    /** @test */
    public function it_does_not_return_any_response_content()
    {
        $user = User::factory()->create();
        InternalNotification::factory()->usingUser($user)->count(3)->create();
        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route);
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    /** @test */
    public function it_marks_all_user_unread_internal_notifications_as_read()
    {
        $user = User::factory()->create();
        InternalNotification::factory()->usingUser($user)->read()->count(3)->create();
        InternalNotification::factory()->usingUser($user)->count(5)->create();
        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(InternalNotification::tableName(), [
            'read_at' => null,
            'user_id' => $user->getKey(),
        ]);
    }

    /** @test */
    public function it_mark_all_unread_app_notifications_for_the_user_as_read()
    {
        $user = User::factory()->create();
        (new AppNotificationFactory())->usingUser($user)
            ->read()
            ->count(3)
            ->create()
            ->pluck((new AppNotification())->getRouteKeyName());
        (new AppNotificationFactory())->usingUser($user)
            ->count(5)
            ->create()
            ->pluck((new AppNotification())->getRouteKeyName());
        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing((new AppNotification())->getTable(), [
            'read'    => false,
            'user_id' => $user->getKey(),
        ]);
    }

    /** @test
     * @throws \Exception
     */
    public function it_sends_an_unread_notification_count_update_notification_with_value_0()
    {
        Notification::fake();

        InternalNotification::flushEventListeners();

        $user = User::factory()->create();
        InternalNotification::factory()->usingUser($user)->read()->count(3)->create();
        InternalNotification::factory()->usingUser($user)->count(5)->create();
        $route = URL::route($this->routeName);

        $this->login($user);
        $response = $this->patch($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        Notification::assertSentTo([$user], UnreadNotificationCount::class,
            function(UnreadNotificationCount $notification) {
                $property = new ReflectionProperty(UnreadNotificationCount::class, 'unreadNotificationsCount');
                $property->setAccessible(true);

                return 0 == $property->getValue($notification);
            });
    }
}
