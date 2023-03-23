<?php

namespace Tests\Feature\Api\V2\Twilio\Webhook\Call\Client\Status;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Events\AgentCall\Answered;
use App\Events\AgentCall\Ringing;
use App\Http\Controllers\Api\V2\Twilio\Webhook\Call\Client\StatusController;
use App\Http\Middleware\ValidateTwilioRequest;
use App\Jobs\DelayUnsolvedTicketNotification;
use App\Jobs\LogCommunicationRequest;
use App\Models\AgentCall;
use App\Models\Call;
use App\Models\Ticket;
use Bus;
use Config;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Api\V2\WithTwilioMiddlewares;
use Tests\TestCase;
use Twilio\Security\RequestValidator;
use URL;

/** @see StatusController */
class StoreTest extends TestCase
{
    use WithTwilioMiddlewares;
    use RefreshDatabase;

    private string           $token     = 'valid';
    private string           $routeName = RouteNames::API_V2_TWILIO_WEBHOOK_CALL_CLIENT_STATUS_STORE;
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
    public function it_logs_the_request()
    {
        $this->withoutExceptionHandling();
        Bus::fake([LogCommunicationRequest::class]);

        $agentCall = AgentCall::factory()->create();

        $data = [
            RequestKeys::TWILIO_PARENT_CALL_SID => $agentCall->call->communication->provider_id,
            RequestKeys::TWILIO_CALL_STATUS     => Call::TWILIO_CALL_STATUS_IN_PROGRESS,
            RequestKeys::TWILIO_UPPER_TO        => 'client:' . $agentCall->agent->getKey(),
        ];

        $response = $this->postToTwilio($data);

        $response->assertStatus(Response::HTTP_CREATED);

        Bus::assertDispatched(LogCommunicationRequest::class);
    }

    /** @test
     * @dataProvider provider
     */
    public function it_stores_agent_call_status(string $twilioStatus, string $expectedStatus)
    {
        $agentCall = AgentCall::factory()->ringing()->create();

        $data = [
            RequestKeys::TWILIO_PARENT_CALL_SID => $agentCall->call->communication->provider_id,
            RequestKeys::TWILIO_CALL_STATUS     => $twilioStatus,
            RequestKeys::TWILIO_UPPER_TO        => 'client:' . $agentCall->agent->getKey(),
        ];

        $response = $this->postToTwilio($data);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas(AgentCall::tableName(), [
            'id'     => $agentCall->getKey(),
            'status' => $expectedStatus,
        ]);
    }

    public function provider(): array
    {
        return [
            [Call::TWILIO_CALL_STATUS_RINGING, AgentCall::STATUS_RINGING],
            [Call::TWILIO_CALL_STATUS_IN_PROGRESS, AgentCall::STATUS_IN_PROGRESS],
            [Call::TWILIO_CALL_STATUS_COMPLETED, AgentCall::STATUS_COMPLETED],
        ];
    }

    /** @test */
    public function it_creates_a_ticket_on_agent_answering()
    {
        $agentCall = AgentCall::factory()->ringing()->create();

        $data     = [
            RequestKeys::TWILIO_PARENT_CALL_SID => $agentCall->call->communication->provider_id,
            RequestKeys::TWILIO_CALL_STATUS     => Call::TWILIO_CALL_STATUS_IN_PROGRESS,
            RequestKeys::TWILIO_UPPER_TO        => 'client:' . $agentCall->agent->getKey(),
        ];
        $response = $this->postToTwilio($data);
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertNotNull($agentCall->call->communication->session->ticket()->first());
    }

    /** @test */
    public function it_notifies_the_agent_on_ringing()
    {
        Event::fake([Ringing::class]);

        $agentCall = AgentCall::factory()->create();

        $data = [
            RequestKeys::TWILIO_PARENT_CALL_SID => $agentCall->call->communication->provider_id,
            RequestKeys::TWILIO_CALL_STATUS     => Call::TWILIO_CALL_STATUS_RINGING,
            RequestKeys::TWILIO_UPPER_TO        => 'client:' . $agentCall->agent->getKey(),
        ];

        $response = $this->postToTwilio($data);
        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched(Ringing::class);
    }

    /** @test */
    public function it_notifies_the_tech_on_agent_answering()
    {
        Event::fake([Answered::class]);

        $agentCall = AgentCall::factory()->ringing()->create();

        $data = [
            RequestKeys::TWILIO_PARENT_CALL_SID => $agentCall->call->communication->provider_id,
            RequestKeys::TWILIO_CALL_STATUS     => Call::TWILIO_CALL_STATUS_IN_PROGRESS,
            RequestKeys::TWILIO_UPPER_TO        => 'client:' . $agentCall->agent->getKey(),
        ];

        $response = $this->postToTwilio($data);
        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched(Answered::class);
    }

    /** @test */
    public function it_completes_the_call_on_twilio_call_status_completed()
    {
        $agentCall = AgentCall::factory()->inProgress()->create();
        $call      = $agentCall->call;

        $data = [
            RequestKeys::TWILIO_PARENT_CALL_SID => $call->communication->provider_id,
            RequestKeys::TWILIO_CALL_STATUS     => Call::TWILIO_CALL_STATUS_COMPLETED,
            RequestKeys::TWILIO_UPPER_TO        => 'client:' . $agentCall->agent->getKey(),
        ];

        $response = $this->postToTwilio($data);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas(AgentCall::tableName(), [
            'id'     => $agentCall->getKey(),
            'status' => AgentCall::STATUS_COMPLETED,
        ]);
    }

    /** @test */
    public function it_enqueue_a_ticket_notification_job_on_twilio_call_status_completed_when_there_is_a_ticket()
    {
        Bus::fake([DelayUnsolvedTicketNotification::class]);
        $agentCall          = AgentCall::factory()->inProgress()->create();
        $session            = $agentCall->call->communication->session;
        $session->ticket_id = Ticket::factory()->create()->getKey();
        $session->save();
        $call = $agentCall->call;

        $data = [
            RequestKeys::TWILIO_PARENT_CALL_SID => $call->communication->provider_id,
            RequestKeys::TWILIO_CALL_STATUS     => Call::TWILIO_CALL_STATUS_COMPLETED,
            RequestKeys::TWILIO_UPPER_TO        => 'client:' . $agentCall->agent->getKey(),
        ];

        $response = $this->postToTwilio($data);
        $response->assertStatus(Response::HTTP_CREATED);

        Bus::assertDispatched(DelayUnsolvedTicketNotification::class);
    }

    /** @test */
    public function it_frees_the_the_agent_on_completed_call()
    {
        $agentCall = AgentCall::factory()->inProgress()->create();
        $call      = $agentCall->call;

        $data = [
            RequestKeys::TWILIO_PARENT_CALL_SID => $call->communication->provider_id,
            RequestKeys::TWILIO_CALL_STATUS     => Call::TWILIO_CALL_STATUS_COMPLETED,
            RequestKeys::TWILIO_UPPER_TO        => 'client:' . $agentCall->agent->getKey(),
        ];

        $response = $this->postToTwilio($data);
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(AgentCall::tableName(), [
            'id'     => $agentCall->getKey(),
            'status' => AgentCall::STATUS_COMPLETED,
        ]);
    }

    private function postToTwilio(array $data = []): TestResponse
    {
        $signature = $this->requestValidator->computeSignature(URL::route($this->routeName), $data);

        return $this->withHeaders([
            ValidateTwilioRequest::TWILIO_HEADER_NAME => $signature,
        ])->post(URL::route($this->routeName), $data);
    }
}
