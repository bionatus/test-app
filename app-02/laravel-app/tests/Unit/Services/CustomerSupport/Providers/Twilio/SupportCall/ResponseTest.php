<?php

namespace Tests\Unit\Services\CustomerSupport\Providers\Twilio\SupportCall;

use App\Constants\RouteNames;
use App\Models\Agent;
use App\Models\Call;
use App\Models\Communication;
use App\Models\Session;
use App\Models\Subject;
use App\Models\Subtopic;
use App\Models\Topic;
use App\Services\CustomerSupport\Call\ResponseInterface;
use App\Services\CustomerSupport\Providers\Twilio\SupportCall\Response;
use ReflectionClass;
use Storage;
use Tests\CanRefreshDatabase;
use Tests\TestCase;
use URL;

class ResponseTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_implements_response_interface()
    {
        $reflection = new ReflectionClass(Response::class);

        $this->assertTrue($reflection->implementsInterface(ResponseInterface::class));
    }

    /** @test */
    public function it_returns_a_retry_again_later_response()
    {
        $response = new Response();

        $rawResponse = $response->retryAgainLater();
        $this->assertIsString($rawResponse);

        $rawTwiml    = simplexml_load_string($rawResponse);
        $objectTwiml = json_decode(json_encode($rawTwiml));

        $this->assertObjectHasAttribute('Say', $objectTwiml);
        $this->assertEquals("We couldn't find an available agent. Please wait a few minutes and try again.",
            $objectTwiml->Say);
    }

    /** @test */
    public function it_returns_a_thanks_response()
    {
        $response = new Response();

        $rawResponse = $response->thanksForCalling();
        $this->assertIsString($rawResponse);

        $rawTwiml = simplexml_load_string($rawResponse);

        $objectTwiml = json_decode(json_encode($rawTwiml));

        $this->assertObjectHasAttribute('Say', $objectTwiml);
        $this->assertEquals('Thank you for calling Bluon support.', $objectTwiml->Say);
    }

    /** @test */
    public function it_returns_a_hangup_response()
    {
        $response = new Response();

        $rawResponse = $response->hangUp();
        $this->assertIsString($rawResponse);

        $rawTwiml    = simplexml_load_string($rawResponse);
        $objectTwiml = json_decode(json_encode($rawTwiml));
        $this->assertObjectHasAttribute('Hangup', $objectTwiml);
    }

    /** @test */
    public function it_returns_a_technical_difficulties_response()
    {
        $response = new Response();

        $rawResponse = $response->technicalDifficulties();
        $this->assertIsString($rawResponse);

        $rawTwiml    = simplexml_load_string($rawResponse);
        $objectTwiml = json_decode(json_encode($rawTwiml));
        $this->assertObjectHasAttribute('Say', $objectTwiml);
        $this->assertEquals('We are very sorry, currently we are experiencing technical difficulties. Please contact us at a later time.',
            $objectTwiml->Say);
    }

    /** @test */
    public function it_returns_a_connect_response_with_a_greeting_message()
    {
        $this->refreshDatabaseForSingleTest();
        $topicSubject    = Subject::factory()->topic()->create(['name' => 'Topic']);
        $topic           = Topic::factory()->usingSubject($topicSubject)->create();
        $subtopicSubject = Subject::factory()->subtopic()->create(['name' => 'Subtopic']);
        $subtopic        = Subtopic::factory()->usingSubject($subtopicSubject)->usingTopic($topic)->create();
        $session         = Session::factory()->usingSubject($subtopic->subject)->create();
        $communication   = Communication::factory()->call()->usingSession($session)->create();
        $call            = Call::factory()->usingCommunication($communication)->create();
        $agent           = Agent::factory()->create();
        $user            = $session->user;

        $response = new Response();

        $rawResponse = $response->connect($call, $call->communication->session->user, $agent);
        $this->assertIsString($rawResponse);

        $rawTwiml = simplexml_load_string($rawResponse);
        $data     = json_decode(json_encode($rawTwiml), true);

        $expected = [
            'Pause' => [
                [
                    '@attributes' => [
                        'length' => 1,
                    ],
                ],
                [
                    '@attributes' => [
                        'length' => 1,
                    ],
                ],
            ],
            'Say'   => 'Welcome to Bluon support. An agent will be with you shortly. Please wait a moment.',
            'Dial'  => [
                '@attributes' => [
                    'callerId'       => "{$call->communication->session->user->getKey()}",
                    'timeLimit'      => '14400',
                    'answerOnBridge' => '1',
                    'timeout'        => '10',
                    'action'         => URL::route(RouteNames::API_V2_TWILIO_WEBHOOK_CALL_ACTION_STORE),
                ],
                'Client'      => [
                    '@attributes' => [
                        'statusCallback'      => URL::route(RouteNames::API_V2_TWILIO_WEBHOOK_CALL_CLIENT_STATUS_STORE),
                        'statusCallbackEvent' => 'ringing answered completed',
                    ],
                    'Identity'    => "{$agent->getKey()}",
                    'Parameter'   => [
                        '@attributes' => [
                            'name'  => 'data',
                            'value' => json_encode([
                                'user'  => [
                                    'id'    => $user->getRouteKey(),
                                    'name'  => $user->name ?? ("{$user->first_name} {$user->last_name}"),
                                    'photo' => !empty($user->photo) ? asset(Storage::url($user->photo)) : null,
                                ],
                                'topic' => [
                                    'id'   => $call->communication->session->subject->getRouteKey(),
                                    'name' => 'Topic/Subtopic',
                                ],
                            ]),
                        ],
                    ],
                ],
            ],
        ];

        $this->assertArrayHasKeysAndValues($expected, $data);
    }

    /** @test */
    public function it_returns_a_connect_response_without_a_greeting_message()
    {
        $this->refreshDatabaseForSingleTest();
        $topicSubject             = Subject::factory()->topic()->create(['name' => 'Topic']);
        $topic                    = Topic::factory()->usingSubject($topicSubject)->create();
        $subtopicSubject          = Subject::factory()->subtopic()->create(['name' => 'Subtopic']);
        $subtopic                 = Subtopic::factory()->usingSubject($subtopicSubject)->usingTopic($topic)->create();
        $session                  = Session::factory()->usingSubject($subtopic->subject)->create();
        $communication            = Communication::factory()->call()->usingSession($session)->create();
        $call                     = Call::factory()->usingCommunication($communication)->create();
        $agent                    = Agent::factory()->create();
        $user                     = $session->user;
        $call->wasRecentlyCreated = false;
        $call->save();

        $response = new Response();

        $rawResponse = $response->connect($call, $call->communication->session->user, $agent);
        $this->assertIsString($rawResponse);

        $rawTwiml = simplexml_load_string($rawResponse);
        $data     = json_decode(json_encode($rawTwiml), true);

        $expected = [
            'Dial' => [
                '@attributes' => [
                    'callerId'       => "{$call->communication->session->user->getKey()}",
                    'timeLimit'      => '14400',
                    'answerOnBridge' => '1',
                    'timeout'        => '10',
                    'action'         => URL::route(RouteNames::API_V2_TWILIO_WEBHOOK_CALL_ACTION_STORE),
                ],
                'Client'      => [
                    '@attributes' => [
                        'statusCallback'      => URL::route(RouteNames::API_V2_TWILIO_WEBHOOK_CALL_CLIENT_STATUS_STORE),
                        'statusCallbackEvent' => 'ringing answered completed',
                    ],
                    'Identity'    => "{$agent->getKey()}",
                    'Parameter'   => [
                        '@attributes' => [
                            'name'  => 'data',
                            'value' => json_encode([
                                'user'  => [
                                    'id'    => $user->getRouteKey(),
                                    'name'  => $user->name ?? ("{$user->first_name} {$user->last_name}"),
                                    'photo' => !empty($user->photo) ? asset(Storage::url($user->photo)) : null,
                                ],
                                'topic' => [
                                    'id'   => $call->communication->session->subject->getRouteKey(),
                                    'name' => 'Topic/Subtopic',
                                ],
                            ]),
                        ],
                    ],
                ],
            ],
        ];

        $this->assertArrayHasKeysAndValues($expected, $data);
    }
}
