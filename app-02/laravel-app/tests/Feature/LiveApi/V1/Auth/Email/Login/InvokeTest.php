<?php

namespace Tests\Feature\LiveApi\V1\Auth\Email\Login;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\Auth\Email\LoginController;
use App\Http\Resources\LiveApi\V1\Auth\Email\Login\BaseResource;
use App\Models\Staff;
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

    private string $routeName = RouteNames::LIVE_API_V1_AUTH_EMAIL_LOGIN;

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->markTestSkipped();
        //$this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_should_login_a_staff()
    {
        $staff = Staff::factory()->createQuietly();

        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::EMAIL    => $staff->email,
            RequestKeys::PASSWORD => 'password',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertAuthenticatedAs($staff);
    }

    /** @test */
    public function it_should_return_a_user_representation()
    {
        $staff = Staff::factory()->createQuietly();

        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::EMAIL    => $staff->email,
            RequestKeys::PASSWORD => 'password',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
    }

    /** @test */
    public function it_should_return_a_valid_jwt_token()
    {
        $staff = Staff::factory()->createQuietly();
        $route = URL::route($this->routeName, [
            RequestKeys::EMAIL    => $staff->email,
            RequestKeys::PASSWORD => 'password',
        ]);

        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        JWTAuth::setToken($response->json('data')['token']);
        $this->assertTrue(JWTAuth::check());
    }

    /** @test */
    public function it_should_fail_login_if_data_is_invalid()
    {
        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::EMAIL    => 'fake.email@example.com',
            RequestKeys::PASSWORD => 'password',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertInvalid([
            'password' => Lang::get('auth.failed'),
        ]);
    }

    /** @test */
    public function it_should_fail_login_if_staff_is_not_owner()
    {
        $staff = Staff::factory()->manager()->withEmail()->createQuietly();

        $response = $this->post(URL::route($this->routeName), [
            RequestKeys::EMAIL    => $staff->email,
            RequestKeys::PASSWORD => 'password',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertInvalid([
            'password' => Lang::get('auth.failed'),
        ]);
    }
}
