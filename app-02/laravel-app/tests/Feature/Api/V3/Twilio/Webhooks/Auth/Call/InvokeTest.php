<?php

namespace Tests\Feature\Api\V3\Twilio\Webhooks\Auth\Call;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Twilio\Webhooks\Auth\CallController;
use App\Http\Middleware\ValidateTwilioRequest;
use App\Models\AuthenticationCode;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Api\V2\WithTwilioMiddlewares;
use Tests\TestCase;
use Twilio\Security\RequestValidator;
use URL;

/** @see CallController */
class InvokeTest extends TestCase
{
    use WithTwilioMiddlewares;
    use RefreshDatabase;

    private string           $token     = 'valid';
    private string           $routeName = RouteNames::API_V3_TWILIO_WEBHOOK_AUTH_CALL;
    private RequestValidator $requestValidator;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('twilio.auth_token', $this->token);
        $this->requestValidator = new RequestValidator($this->token);
    }

    /** @test */
    public function an_unauthenticated_request_can_not_proceed()
    {
        $response = $this->post(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function it_generates_a_say_twiml()
    {
        $authenticationCode = AuthenticationCode::factory()->verification()->create();
        $phone              = $authenticationCode->phone;

        $data = [
            RequestKeys::TWILIO_UPPER_TO => '+' . $phone->fullNumber(),
        ];

        $response = $this->postToTwilio($data);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertValidXML($response->content());

        $rawTwiml    = simplexml_load_string($response->getContent());
        $objectTwiml = json_decode(json_encode($rawTwiml));
        $this->assertEquals('Response', $rawTwiml->getName());
        $this->assertValidSchema($this->successSchema(), $objectTwiml);
    }

    /** @test */
    private function successSchema(): array
    {
        return [
            'type'                 => 'object',
            'properties'           => [
                'Pause' => [
                    'type'  => ['array'],
                    'items' => [
                        'type'                 => ['object'],
                        'properties'           => [
                            '@attributes' => [
                                'type'  => ['object'],
                                'items' => [
                                    'type'                 => ['object'],
                                    'properties'           => [
                                        'length' => ['type' => ['integer']],
                                    ],
                                    'required'             => ['length'],
                                    'additionalAttributes' => false,
                                ],
                            ],
                        ],
                        'required'             => ['@attributes'],
                        'additionalAttributes' => false,
                    ],
                ],
                'Say'   => [
                    'type'  => ['array'],
                    'items' => [
                        'type' => ['string'],
                    ],
                ],
            ],
            'required'             => ['Pause', 'Say'],
            'additionalAttributes' => false,
        ];
    }

    private function postToTwilio(array $data = []): TestResponse
    {
        $signature = $this->requestValidator->computeSignature(URL::route($this->routeName), $data);

        return $this->withHeaders([
            ValidateTwilioRequest::TWILIO_HEADER_NAME => $signature,
        ])->post(URL::route($this->routeName), $data);
    }
}
