<?php

namespace Tests\Feature\Api\V2\Twilio\Token;

use App\Constants\OperatingSystems;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Requests\Api\V2\Twilio\Token\StoreRequest;
use App\Http\Resources\Api\V2\Twilio\Token\BaseResource;
use App\Models\User;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MohammedManssour\FormRequestTester\TestsFormRequests;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see TokenController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use TestsFormRequests;
    use WithLatamMiddlewares;

    private string $routeName                = RouteNames::API_V2_TWILIO_TOKEN_STORE;
    private string $accountSid               = 'account';
    private string $apiKey                   = 'key';
    private string $apiSecret                = 'secret';
    private string $appSid                   = 'appSid';
    private string $androidPushCredentialSid = 'pushSid';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('twilio.account_sid', $this->accountSid);
        Config::set('twilio.api_key', $this->apiKey);
        Config::set('twilio.api_secret', $this->apiSecret);
        Config::set('twilio.app_sid', $this->appSid);
        Config::set('twilio.android_push_credential_sid', $this->androidPushCredentialSid);
    }

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_generates_a_token()
    {
        $user = User::factory()->create();

        $this->login($user);
        $response = $this->post(URL::route($this->routeName), [RequestKeys::OS => OperatingSystems::ANDROID]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false, false), $response);

        $jwt                 = Collection::make(explode('.', json_decode($response->getContent())->token));
        $incomingSignature   = $jwt->get(2);
        $calculatedSignature = hash_hmac('sha256', $jwt->get(0) . '.' . $jwt->get(1), $this->apiSecret, true);
        $calculatedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($calculatedSignature));

        $this->assertEquals($calculatedSignature, $incomingSignature);

        $explodedJwt = $jwt->map(function ($item) {
            return json_decode(base64_decode($item));
        });

        $header = $explodedJwt->get(0);
        $this->assertValidSchema($this->headerSchema(), $header);

        $payload = $explodedJwt->get(1);
        $expectedTtl = Carbon::now()->addHours(10);
        $ttl = Carbon::createFromTimestamp($payload->exp);
        $this->assertValidSchema($this->payloadSchema(), $payload);
        $this->assertTrue($ttl->between($expectedTtl->clone()->subMinute(), $expectedTtl->clone()->addMinute()));
    }

    protected function headerSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'typ' => ['type' => 'string', 'const' => 'JWT'],
                'alg' => ['type' => 'string', 'const' => 'HS256'],
                'cty' => ['type' => 'string', 'const' => 'twilio-fpa;v=1'],
            ],
        ];
    }

    protected function payloadSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'jti'    => ['type' => 'string'],
                'iss'    => ['type' => 'string', 'const' => $this->apiKey],
                'sub'    => ['type' => 'string', 'const' => $this->accountSid],
                'exp'    => ['type' => 'integer'],
                'grants' => [
                    'type'       => 'object',
                    'properties' => [
                        'identity' => ['type' => 'string'],
                        'voice'    => [
                            'type'       => 'object',
                            'properties' => [
                                'incoming'            => [
                                    'type'       => 'object',
                                    'properties' => [
                                        'allow' => ['type' => 'boolean', 'const' => true],
                                    ],
                                ],
                                'outgoing'            => [
                                    'type'       => 'object',
                                    'properties' => [
                                        'application_sid' => ['type' => 'string', 'const' => $this->appSid],
                                    ],
                                ],
                                'push_credential_sid' => [
                                    'type'  => ['string'],
                                    'const' => $this->androidPushCredentialSid,
                                ],
                            ],
                            'required'   => ['incoming', 'outgoing', 'push_credential_sid'],
                        ],
                    ],
                    'required'   => ['identity', 'voice'],
                ],
            ],
            'required'   => ['jti', 'iss', 'sub', 'exp', 'grants'],
        ];
    }
}
