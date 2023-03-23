<?php

namespace Tests\Feature\Api\V3\Auth\Phone\Register\Verify;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Auth\Phone\Register\VerifyController;
use App\Http\Requests\Api\V3\Auth\Phone\Register\Verify\InvokeRequest;
use App\Http\Resources\Api\V3\Auth\Phone\Register\Verify\BaseResource;
use App\Models\AuthenticationCode;
use App\Models\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see VerifyController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_AUTH_PHONE_REGISTER_VERIFY;

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_should_return_the_phone_number()
    {
        $code  = 123456;
        $phone = Phone::factory()->create();
        AuthenticationCode::factory()->usingPhone($phone)->verification()->create(['code' => $code]);

        $response = $this->post(URL::route($this->routeName, $phone->fullNumber()), [
            RequestKeys::CODE => $code,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($phone->fullNumber(), $data['id']);
    }

    /** @test */
    public function it_should_return_a_valid_jwt_token()
    {
        $code  = 123456;
        $phone = Phone::factory()->create();
        AuthenticationCode::factory()->usingPhone($phone)->verification()->create(['code' => $code]);

        $response = $this->post(URL::route($this->routeName, $phone->fullNumber()), [
            RequestKeys::CODE => $code,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);

        JWTAuth::setToken($response->json('data')['token']);
        $this->assertTrue(JWTAuth::check());
    }

    /** @test */
    public function it_should_remove_verify_authentication_codes()
    {
        $code               = 123456;
        $phone              = Phone::factory()->create();
        $authenticationCode = AuthenticationCode::factory()
            ->usingPhone($phone)
            ->verification()
            ->create(['code' => $code]);
        AuthenticationCode::factory()->usingPhone($phone)->login()->create();

        $this->post(URL::route($this->routeName, $phone->fullNumber()), [
            RequestKeys::CODE => $code,
        ]);

        $this->assertDeleted($authenticationCode);
        $this->assertDatabaseCount(AuthenticationCode::tableName(), 1);
    }
}
