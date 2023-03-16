<?php

namespace Tests\Feature\Api\V2\Twilio\Webhook\Call\Fallback;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Middleware\ValidateTwilioRequest;
use App\Jobs\LogCommunicationRequest;
use App\Models\Call;
use Bus;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Api\V2\WithTwilioMiddlewares;
use Tests\TestCase;
use Twilio\Security\RequestValidator;
use URL;

/** @see FallbackController */
class StoreTest extends TestCase
{
    use WithTwilioMiddlewares;
    use RefreshDatabase;

    private string           $token     = 'valid';
    private string           $routeName = RouteNames::API_V2_TWILIO_WEBHOOK_CALL_FALLBACK_STORE;
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
        Bus::fake([LogCommunicationRequest::class]);

        $call = Call::factory()->create();

        $data = [
            RequestKeys::TWILIO_CALL_SID => $call->communication->provider_id,
        ];

        $response = $this->postToTwilio($data);

        $response->assertStatus(Response::HTTP_CREATED);

        Bus::assertDispatched(LogCommunicationRequest::class);
    }

    /** @test */
    public function it_completes_the_call()
    {
        $call = Call::factory()->inProgress()->create();

        $data = [
            RequestKeys::TWILIO_CALL_SID => $call->communication->provider_id,
        ];

        $response = $this->postToTwilio($data);
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas(Call::tableName(), [
            'id'     => $call->getKey(),
            'status' => Call::STATUS_COMPLETED,
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
