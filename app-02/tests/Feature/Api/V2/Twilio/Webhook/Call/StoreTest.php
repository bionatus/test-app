<?php

namespace Tests\Feature\Api\V2\Twilio\Webhook\Call;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V2\Twilio\Webhook\CallController;
use App\Http\Middleware\ValidateTwilioRequest;
use App\Jobs\LogCommunicationRequest;
use App\Models\Agent;
use App\Models\Setting;
use App\Models\SettingUser;
use App\Models\Subject;
use App\Models\User;
use Bus;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Api\V2\WithTwilioMiddlewares;
use Tests\TestCase;
use Twilio\Security\RequestValidator;
use URL;

/** @see CallController */
class StoreTest extends TestCase
{
    use WithTwilioMiddlewares;
    use RefreshDatabase;

    private string           $token     = 'valid';
    private string           $routeName = RouteNames::API_V2_TWILIO_WEBHOOK_CALL_STORE;
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
    public function it_generates_a_retry_when_there_is_no_available_agent()
    {
        $data = [
            RequestKeys::TWILIO_LOWER_FROM => User::factory()->create()->getKey(),
            RequestKeys::TWILIO_CALL_SID   => 'CA123',
            RequestKeys::TWILIO_LOWER_TO   => Subject::factory()->create()->getRouteKey(),
        ];

        $response = $this->postToTwilio($data);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertValidXML($response->content());

        $expected = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Say>We couldn't find an available agent. Please wait a few minutes and try again.</Say></Response>\n";
        $this->assertSame($expected, $response->content());
    }

    /** @test */
    public function it_generates_a_dial_twiml()
    {
        $agent   = Agent::factory()->create();
        $setting = Setting::factory()->agentAvailable()->create();
        SettingUser::factory()->usingSetting($setting)->usingUser($agent->user)->create(['value' => true]);

        $data = [
            RequestKeys::TWILIO_LOWER_FROM => User::factory()->create()->getKey(),
            RequestKeys::TWILIO_CALL_SID   => 'CA123',
            RequestKeys::TWILIO_LOWER_TO   => Subject::factory()->create()->getRouteKey(),
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
    public function it_logs_the_request()
    {
        Bus::fake([LogCommunicationRequest::class]);

        $agent   = Agent::factory()->create();
        $setting = Setting::factory()->agentAvailable()->create();
        SettingUser::factory()->usingSetting($setting)->usingUser($agent->user)->create(['value' => true]);

        $data = [
            RequestKeys::TWILIO_LOWER_FROM => User::factory()->create()->getKey(),
            RequestKeys::TWILIO_CALL_SID   => 'CA123',
            RequestKeys::TWILIO_LOWER_TO   => Subject::factory()->create()->getRouteKey(),
        ];

        $this->postToTwilio($data);

        Bus::assertDispatched(LogCommunicationRequest::class);
    }

    private function successSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'Say'  => ['type' => ['string']],
                'Dial' => [
                    'type'       => 'object',
                    'properties' => [
                        '@attributes' => [
                            'type'       => 'object',
                            'properties' => [
                                'callerId'       => ['type' => 'string'],
                                'timeLimit'      => ['type' => ['string']],
                                'answerOnBridge' => ['type' => ['string']],
                                'timeout'        => ['type' => ['string']],
                                'action'         => ['type' => ['string']],
                            ],
                            'required'   => ['callerId', 'timeLimit', 'answerOnBridge', 'timeout', 'action'],
                        ],
                        'Client'      => [
                            'type'       => ['object'],
                            'properties' => [
                                '@attributes' => [
                                    'type'       => ['object'],
                                    'properties' => [
                                        'statusCallback'      => ['type' => ['string']],
                                        'statusCallbackEvent' => ['type' => ['string']],
                                    ],
                                    'required'   => ['statusCallback', 'statusCallbackEvent'],
                                ],
                                'Identity'    => ['type' => ['string']],
                                'Parameter'   => [
                                    'type'       => ['object'],
                                    'properties' => [
                                        '@attributes' => [
                                            'type'       => ['object'],
                                            'properties' => [
                                                'name'  => ['type' => ['string']],
                                                'value' => ['type' => ['string']],
                                            ],
                                            'required'   => ['name', 'value'],
                                        ],
                                    ],
                                    'required'   => ['@attributes'],
                                ],
                            ],
                            'required'   => ['@attributes', 'Identity', 'Parameter'],
                        ],
                    ],
                    'required'   => ['@attributes', 'Client'],
                ],
            ],
            'required'   => ['Say', 'Dial'],
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
