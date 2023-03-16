<?php

namespace Tests\Feature\AutomationApi\V1\Mobile\SignupProcess;

use App\Constants\RouteNames;
use App\Http\Controllers\AutomationApi\V1\Mobile\SignupProcessController;
use App\Http\Resources\AutomationApi\V1\Mobile\SignupProcess\BaseResource;
use App\Models\AuthenticationCode;
use App\Models\Phone;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see SignupProcessController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::AUTOMATION_API_V1_MOBILE_SIGNUP_PROCESS;

    /** @test */
    public function it_displays_an_authentication_code()
    {
        $phone              = Phone::factory()->create();
        $authenticationCode = AuthenticationCode::factory()->verification()->usingPhone($phone)->create();
        $route              = URL::route($this->routeName, [$phone->fullNumber()]);

        $key = 'test_key';
        Config::set('automation.token.key', $key);
        $token    = Hash::make($key);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema());
        $this->validateResponseSchema($schema, $response);

        $data = $response->json('data');
        $this->assertEquals($data['code'], $authenticationCode->code);
    }
}
