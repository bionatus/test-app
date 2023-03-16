<?php

namespace Tests\Feature\Api\V3\Auth\Refresh;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Auth\RefreshController;
use App\Http\Resources\Api\V3\Auth\Refresh\BaseResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see RefreshController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_AUTH_REFRESH;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_invalidates_the_users_previous_jwt_token()
    {
        $user  = User::factory()->create();
        $route = URL::route($this->routeName);

        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', 'Bearer ' . $token);
        $this->actingAs($user);

        JWTAuth::setToken($token);

        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertFalse(JWTAuth::check());
    }

    /** @test */
    public function it_should_return_a_user_representation()
    {
        $user  = User::factory()->photo()->create();
        $route = URL::route($this->routeName);

        $this->login($user);

        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);
    }

    /** @test */
    public function it_should_return_a_valid_jwt_token()
    {
        $user  = User::factory()->create();
        $route = URL::route($this->routeName);

        $token = JWTAuth::fromUser($user);
        $this->withHeader('Authorization', 'Bearer ' . $token);
        $this->actingAs($user);

        JWTAuth::setToken($token);

        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);

        JWTAuth::setToken($response->json('data')['token']);
        $this->assertTrue(JWTAuth::check());
    }
}
