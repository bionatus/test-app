<?php

namespace Tests\Feature\Api\V3\Auth\Logout;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Auth\LogoutController;
use App\Models\Device;
use App\Models\User;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use URL;

/** @see LogoutController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_AUTH_LOGOUT;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->delete(URL::route($this->routeName));
    }

    /** @test */
    public function it_should_logout_a_user()
    {
        $user  = User::factory()->create();

        $this->login($user);

        $response = $this->delete(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertNull(Auth::user());
    }

    /** @test */
    public function it_should_delete_the_devices_associated_with_the_jwt_token()
    {
        $user  = User::factory()->create();
        $route = URL::route($this->routeName);

        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', 'Bearer ' . $token);
        $this->actingAs($user);

        Device::factory()->usingUser($user)->count(2)->create(['token' => $token]);
        Device::factory()->count(5)->create(['token' => $token]);

        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDeleted(Device::tableName(), ['user_id' => $user->getKey(), 'token' => $token]);
        $this->assertDatabaseCount(Device::tableName(), 5);
    }

    /** @test */
    public function it_invalidates_the_user_jwt_token()
    {
        $user  = User::factory()->create();
        $route = URL::route($this->routeName);

        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', 'Bearer ' . $token);
        $this->actingAs($user);

        JWTAuth::setToken($token);

        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->expectException(TokenBlacklistedException::class);
        JWTAuth::blacklist()->has(JWTAuth::getPayload());
    }
}
