<?php

namespace Tests\Unit\Http\Requests\Api\V2\Twilio\Webhook\Call\Client\Status;

use App;
use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V2\Twilio\Webhook\Call\Client\StatusController;
use App\Http\Requests\Api\V2\Twilio\Webhook\Call\Client\Status\StoreRequest;
use App\Http\Requests\Api\V2\Twilio\Webhook\Call\Client\Status\StoreRequestValidationException;
use App\Models\Agent;
use App\Models\AgentCall;
use App\Models\Call;
use App\Rules\AgentCall\Exists;
use Illuminate\Foundation\Application;
use Lang;
use Mockery;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see StatusController */
class StoreRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function it_requires_a_parent_call_sid_parameter()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_PARENT_CALL_SID]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::TWILIO_PARENT_CALL_SID]),
        ]);
    }

    /** @test */
    public function its_parent_call_sid_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TWILIO_PARENT_CALL_SID => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_PARENT_CALL_SID]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => RequestKeys::TWILIO_PARENT_CALL_SID]),
        ]);
    }

    /** @test */
    public function its_parent_call_sid_parameter_must_belong_to_an_existing_call()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass, [RequestKeys::TWILIO_PARENT_CALL_SID => 'CA123']);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_PARENT_CALL_SID]);
        $request->assertValidationMessages([
            Lang::get('validation.exists', ['attribute' => RequestKeys::TWILIO_PARENT_CALL_SID]),
        ]);
    }

    /** @test */
    public function it_requires_a_to_parameter()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_UPPER_TO]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::TWILIO_UPPER_TO]),
        ]);
    }

    /** @test */
    public function its_to_parameter_needs_to_belong_to_an_agent()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TWILIO_UPPER_TO => 'invalid',
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_UPPER_TO]);
        $request->assertValidationMessages([
            Lang::get('validation.exists', ['attribute' => RequestKeys::TWILIO_UPPER_TO]),
        ]);
    }

    /** @test */
    public function its_to_parameter_needs_to_belong_to_an_agent_call()
    {
        $this->refreshDatabaseForSingleTest();

        $call  = Call::factory()->create();
        $agent = Agent::factory()->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TWILIO_PARENT_CALL_SID => $call->communication->provider_id,
            RequestKeys::TWILIO_UPPER_TO        => 'client:' . $agent->getKey(),
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_UPPER_TO]);
        $message = (new Exists(new \App\Rules\Call\Exists('provider')))->message();
        $request->assertValidationMessages([Lang::get($message, ['attribute' => RequestKeys::TWILIO_UPPER_TO])]);
    }

    /** @test */
    public function it_returns_the_agent_call()
    {
        $mock = Mockery::mock(Exists::class);
        $mock->shouldReceive('agentCall')->withNoArgs()->once()->andReturn($agentCall = new AgentCall());
        App::bind(Exists::class, function() use ($mock) {
            return $mock;
        });
        $request = StoreRequest::create('', 'POST', []);

        $this->assertSame($agentCall, $request->agentCall());
    }

    /** @test */
    public function it_requires_a_call_status_parameter()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_CALL_STATUS]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::TWILIO_CALL_STATUS]),
        ]);
    }

    /** @test */
    public function its_call_status_parameter_must_not_be_outside_of_a_specific_list()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TWILIO_CALL_STATUS => 'invalid']);

        $request->assertValidationErrors([RequestKeys::TWILIO_CALL_STATUS]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => RequestKeys::TWILIO_CALL_STATUS]),
        ]);
    }

    /**
     * @test
     * @dataProvider callStatusProvider
     */
    public function its_call_status_parameter_must_not_be_in_a_specific_list(string $status)
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TWILIO_CALL_STATUS => $status]);

        $request->assertValidationErrorsMissing([RequestKeys::TWILIO_CALL_STATUS]);
    }

    public function callStatusProvider(): array
    {
        return [
            [Call::TWILIO_CALL_STATUS_QUEUED],
            [Call::TWILIO_CALL_STATUS_INITIATED],
            [Call::TWILIO_CALL_STATUS_RINGING],
            [Call::TWILIO_CALL_STATUS_IN_PROGRESS],
            [Call::TWILIO_CALL_STATUS_BUSY],
            [Call::TWILIO_CALL_STATUS_FAILED],
            [Call::TWILIO_CALL_STATUS_NO_ANSWER],
            [Call::TWILIO_CALL_STATUS_COMPLETED],
        ];
    }

    /** @test
     * @dataProvider mapStatusProvider
     */
    public function it_maps_call_statuses(string $twilioStatus, string $modelStatus)
    {
        $data = [
            RequestKeys::TWILIO_CALL_STATUS => $twilioStatus,
        ];

        $request = StoreRequest::create('', 'POST', $data);

        $this->assertEquals($modelStatus, $request->status());
    }

    public function mapStatusProvider(): array
    {
        return [
            [Call::TWILIO_CALL_STATUS_QUEUED, AgentCall::STATUS_RINGING],
            [Call::TWILIO_CALL_STATUS_INITIATED, AgentCall::STATUS_RINGING],
            [Call::TWILIO_CALL_STATUS_RINGING, AgentCall::STATUS_RINGING],
            [Call::TWILIO_CALL_STATUS_IN_PROGRESS, AgentCall::STATUS_IN_PROGRESS],
            [Call::TWILIO_CALL_STATUS_COMPLETED, AgentCall::STATUS_COMPLETED],
            [Call::TWILIO_CALL_STATUS_BUSY, AgentCall::STATUS_DROPPED],
            [Call::TWILIO_CALL_STATUS_FAILED, AgentCall::STATUS_DROPPED],
            [Call::TWILIO_CALL_STATUS_NO_ANSWER, AgentCall::STATUS_DROPPED],
            ['unknown', Call::STATUS_INVALID],
        ];
    }

    /** @test */
    public function it_throw_a_custom_exception_on_validation_failed()
    {
        $this->refreshDatabaseForSingleTest();

        $this->expectException(StoreRequestValidationException::class);

        $request = StoreRequest::create('', 'POST', []);
        $request->setContainer(App::make(Application::class));
        $request->validateResolved();
    }
}
