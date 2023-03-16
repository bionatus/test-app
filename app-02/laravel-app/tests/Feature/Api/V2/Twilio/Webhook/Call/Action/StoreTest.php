<?php

namespace Tests\Feature\Api\V2\Twilio\Webhook\Call\Action;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V2\Twilio\Webhook\Call\ActionController;
use App\Http\Middleware\ValidateTwilioRequest;
use App\Jobs\LogCommunicationRequest;
use App\Models\Agent;
use App\Models\Call;
use App\Models\Setting;
use App\Models\SettingUser;
use Bus;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Api\V2\WithTwilioMiddlewares;
use Tests\TestCase;
use Twilio\Security\RequestValidator;
use URL;

/** @see ActionController */
class StoreTest extends TestCase
{
    use WithTwilioMiddlewares;
    use RefreshDatabase;

    private string           $token     = 'valid';
    private string           $routeName = RouteNames::API_V2_TWILIO_WEBHOOK_CALL_ACTION_STORE;
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
    public function it_generates_a_hangup_twiml_and_logs_the_request()
    {
        Bus::fake([LogCommunicationRequest::class]);

        $call          = Call::factory()->create();
        $communication = $call->communication;

        $data = [
            RequestKeys::TWILIO_CALL_SID         => $communication->provider_id,
            RequestKeys::TWILIO_CALL_STATUS      => Call::TWILIO_CALL_STATUS_COMPLETED,
            RequestKeys::TWILIO_DIAL_CALL_STATUS => Call::TWILIO_DIAL_CALL_STATUS_COMPLETED,
        ];

        $response = $this->postToTwilio($data);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertValidXML($response->content());
        $expected = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Hangup/></Response>\n";
        $this->assertSame($expected, $response->content());

        Bus::assertDispatched(LogCommunicationRequest::class);
    }

    /** @test */
    public function it_generates_a_thanks_twiml_and_logs_the_request()
    {
        Bus::fake([LogCommunicationRequest::class]);

        $call          = Call::factory()->create();
        $communication = $call->communication;

        $data = [
            RequestKeys::TWILIO_CALL_SID         => $communication->provider_id,
            RequestKeys::TWILIO_CALL_STATUS      => Call::TWILIO_CALL_STATUS_IN_PROGRESS,
            RequestKeys::TWILIO_DIAL_CALL_STATUS => Call::TWILIO_DIAL_CALL_STATUS_COMPLETED,
        ];

        $response = $this->postToTwilio($data);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertValidXML($response->content());
        $expected = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Say>Thank you for calling Bluon support.</Say></Response>\n";
        $this->assertSame($expected, $response->content());

        Bus::assertDispatched(LogCommunicationRequest::class);
    }

    /** @test */
    public function it_generates_a_dial_twiml_and_logs_the_request()
    {
        $this->withoutExceptionHandling();
        Bus::fake([LogCommunicationRequest::class]);

        $agent   = Agent::factory()->create();
        $setting = Setting::factory()->agentAvailable()->create();
        SettingUser::factory()->usingSetting($setting)->usingUser($agent->user)->create(['value' => true]);
        $call          = Call::factory()->create();
        $communication = $call->communication;

        $data = [
            RequestKeys::TWILIO_CALL_SID         => $communication->provider_id,
            RequestKeys::TWILIO_CALL_STATUS      => Call::TWILIO_CALL_STATUS_IN_PROGRESS,
            RequestKeys::TWILIO_DIAL_CALL_STATUS => Call::TWILIO_DIAL_CALL_STATUS_NO_ANSWER,
        ];

        $response = $this->postToTwilio($data);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertValidXML($response->content());

        $rawTwiml    = simplexml_load_string($response->getContent());
        $objectTwiml = json_decode(json_encode($rawTwiml));
        $this->assertEquals('Response', $rawTwiml->getName());
        $this->assertValidSchema($this->successSchema(), $objectTwiml);

        Bus::assertDispatched(LogCommunicationRequest::class);
    }

    /** @test */
    public function it_generates_a_retry_when_there_is_no_available_agent()
    {
        $call          = Call::factory()->create();
        $communication = $call->communication;

        $data = [
            RequestKeys::TWILIO_CALL_SID         => $communication->provider_id,
            RequestKeys::TWILIO_CALL_STATUS      => Call::TWILIO_CALL_STATUS_IN_PROGRESS,
            RequestKeys::TWILIO_DIAL_CALL_STATUS => Call::TWILIO_DIAL_CALL_STATUS_NO_ANSWER,
        ];

        $response = $this->postToTwilio($data);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertValidXML($response->content());

        $expected = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Say>We couldn't find an available agent. Please wait a few minutes and try again.</Say></Response>\n";
        $this->assertSame($expected, $response->content());
    }

    private function successSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
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
            'required'   => ['Dial'],
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
