<?php

namespace Tests\Feature\Api\V2\PushNotificationToken;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Requests\Api\V2\PushNotificationToken\StoreRequest;
use App\Http\Resources\Api\V2\PushNotificationToken\BaseResource;
use App\Models\PushNotificationToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MohammedManssour\FormRequestTester\TestsFormRequests;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see PushNotificationTokenController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use TestsFormRequests;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_PUSH_NOTIFICATION_TOKEN_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $route = URL::route($this->routeName);

        $this->expectException(UnauthorizedHttpException::class);

        $this->post($route);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_stores_a_push_notification_token()
    {
        $token   = 'a valid token';
        $device  = 'a valid device udid';
        $version = '1.2.3';
        $route   = URL::route($this->routeName);

        $this->login();

        $response = $this->post($route, [
            RequestKeys::OS      => PushNotificationToken::OS_ANDROID,
            RequestKeys::DEVICE  => $device,
            RequestKeys::VERSION => $version,
            RequestKeys::TOKEN   => $token,
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $data = $response->json('data');
        $this->assertDatabaseHas(PushNotificationToken::tableName(), [
            'os'    => PushNotificationToken::OS_ANDROID,
            'token' => $token,
        ]);
        $this->assertArrayHasKeysAndValues([
            'os' => PushNotificationToken::OS_ANDROID,
            'device' => $device,
        ], (array) $data);
    }
}
