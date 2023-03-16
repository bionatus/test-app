<?php

namespace Tests\Feature\Api\V3\Auth\Email\Login;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Auth\Email\LoginController;
use App\Http\Requests\Api\V3\Auth\Email\Login\InvokeRequest;
use App\Http\Resources\Api\V3\Auth\Email\Login\BaseResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use JWTAuth;
use Lang;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see LoginController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_AUTH_EMAIL_LOGIN;

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_should_login_a_user()
    {
        $user = User::factory()->create();

        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::EMAIL    => $user->email,
            RequestKeys::PASSWORD => 'password',
            RequestKeys::DEVICE   => 'a valid device',
            RequestKeys::VERSION  => '1.2.3',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_should_return_a_user_representation()
    {
        $user = User::factory()->photo()->create();

        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::EMAIL    => $user->email,
            RequestKeys::PASSWORD => 'password',
            RequestKeys::DEVICE   => 'a valid device',
            RequestKeys::VERSION  => '1.2.3',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);
    }

    /** @test */
    public function it_should_return_a_valid_jwt_token()
    {
        $user  = User::factory()->photo()->create();
        $route = URL::route($this->routeName, [
            RequestKeys::EMAIL    => $user->email,
            RequestKeys::PASSWORD => 'password',
            RequestKeys::DEVICE   => 'a valid device',
            RequestKeys::VERSION  => '1.2.3',
        ]);

        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        JWTAuth::setToken($response->json('data')['token']);
        $this->assertTrue(JWTAuth::check());
    }

    /** @test */
    public function it_should_fail_login_if_data_is_invalid()
    {
        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::EMAIL    => 'fake.email@example.com',
            RequestKeys::PASSWORD => 'password',
            RequestKeys::DEVICE   => 'a valid device',
            RequestKeys::VERSION  => '1.2.3',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertInvalid([
            'password' => Lang::get('auth.failed'),
        ]);
    }

    /** @test */
    public function it_should_fail_login_if_user_is_disabled()
    {
        $user = User::factory()->create(['disabled_at' => Carbon::now()]);

        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::EMAIL    => $user->email,
            RequestKeys::PASSWORD => 'password',
            RequestKeys::DEVICE   => 'a valid device',
            RequestKeys::VERSION  => '1.2.3',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertInvalid([
            'email' => 'The account has been disabled.',
        ]);
    }
}
