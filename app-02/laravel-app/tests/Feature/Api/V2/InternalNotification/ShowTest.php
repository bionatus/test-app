<?php

namespace Tests\Feature\Api\V2\InternalNotification;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V2\InternalNotificationController;
use App\Http\Resources\Api\V2\InternalNotification\BaseResource;
use App\Models\InternalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see InternalNotificationController */
class ShowTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V2_INTERNAL_NOTIFICATION_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();
        $this->expectException(UnauthorizedHttpException::class);

        $internalNotification = InternalNotification::factory()->create();
        $route                = URL::route($this->routeName, $internalNotification);

        $this->get($route);
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:read,' . RouteParameters::INTERNAL_NOTIFICATION]);
    }

    /** @test */
    public function it_marks_an_internal_notification_as_read()
    {
        $internalNotification = InternalNotification::factory()->create();
        $route                = URL::route($this->routeName, $internalNotification);

        $this->login($internalNotification->user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertNotNull($internalNotification->fresh()->read_at);
    }

    /** @test */
    public function it_does_not_change_the_read_date_if_the_notification_is_read()
    {
        $readDate             = Carbon::now()->subDay();
        $internalNotification = InternalNotification::factory()->create(['read_at' => $readDate]);
        $route                = URL::route($this->routeName, $internalNotification);

        $this->login($internalNotification->user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals($readDate->toIso8601String(), $internalNotification->fresh()->read_at->toIso8601String());
    }

    /** @test */
    public function it_displays_an_internal_notification()
    {
        $internalNotification = InternalNotification::factory()->create();
        $route                = URL::route($this->routeName, $internalNotification);

        $this->login($internalNotification->user);
        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);

        $data     = $response->json('data');
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);
        $this->assertSame($internalNotification->getRouteKey(),$data['id']);
    }
}
