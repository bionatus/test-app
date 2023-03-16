<?php

namespace Tests\Feature\Api\V3\Auth\Phone\Login\Verify;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Auth\Phone\Login\VerifyController;
use App\Http\Requests\Api\V3\Auth\Phone\Login\Verify\InvokeRequest;
use App\Http\Resources\Api\V3\Auth\Phone\Register\Assign\BaseResource;
use App\Models\AuthenticationCode;
use App\Models\Device;
use App\Models\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see VerifyController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_AUTH_PHONE_LOGIN_VERIFY;

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_should_return_a_user_representation()
    {
        $device             = Device::factory()->create(['udid' => 'a valid device']);
        $phone              = Phone::factory()->usingUser($device->user)->verified()->create(['id' => 200]);
        $authenticationCode = AuthenticationCode::factory()->usingPhone($phone)->login()->create();

        $response = $this->post(URL::route($this->routeName, $phone->fullNumber()), [
            RequestKeys::CODE    => $authenticationCode->code,
            RequestKeys::DEVICE  => $device->udid,
            RequestKeys::VERSION => '5.2.0',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);
    }

    /** @test */
    public function it_should_return_a_valid_jwt_token()
    {
        $device             = Device::factory()->create(['udid' => 'a valid device']);
        $phone              = Phone::factory()->usingUser($device->user)->verified()->create(['id' => 200]);
        $authenticationCode = AuthenticationCode::factory()->usingPhone($phone)->login()->create();

        $response = $this->post(URL::route($this->routeName, $phone->fullNumber()), [
            RequestKeys::CODE    => $authenticationCode->code,
            RequestKeys::DEVICE  => $device->udid,
            RequestKeys::VERSION => '5.2.0',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        JWTAuth::setToken($response->json('data')['token']);
        $this->assertTrue(JWTAuth::check());
    }
}
