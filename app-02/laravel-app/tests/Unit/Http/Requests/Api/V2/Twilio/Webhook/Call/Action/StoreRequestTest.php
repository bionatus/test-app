<?php

namespace Tests\Unit\Http\Requests\Api\V2\Twilio\Webhook\Call\Action;

use App;
use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V2\Twilio\Webhook\Call\ActionController;
use App\Http\Requests\Api\V2\Twilio\Webhook\Call\Action\StoreRequest;
use App\Http\Requests\Api\V2\Twilio\Webhook\Call\Action\StoreRequestValidationException;
use App\Models\Call;
use App\Rules\Call\Exists;
use App\Rules\Call\NotExpired;
use Config;
use Illuminate\Foundation\Application;
use Illuminate\Support\Carbon;
use Lang;
use Mockery;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see ActionController */
class StoreRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function it_requires_a_call_sid_parameter()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_CALL_SID]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::TWILIO_CALL_SID]),
        ]);
    }

    /** @test */
    public function its_call_sid_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TWILIO_CALL_SID => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_CALL_SID]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => RequestKeys::TWILIO_CALL_SID]),
        ]);
    }

    /** @test */
    public function its_call_sid_parameter_must_belong_to_an_existing_call()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass, [RequestKeys::TWILIO_CALL_SID => 'CA123']);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_CALL_SID]);
        $request->assertValidationMessages([
            Lang::get('validation.exists', ['attribute' => RequestKeys::TWILIO_CALL_SID]),
        ]);
    }

    /** @test */
    public function its_call_sid_parameter_must_belong_to_a_not_expired_call()
    {
        $this->refreshDatabaseForSingleTest();

        $maxTechWaitingTime = (int) Config::get('communications.calls.max_user_waiting_time');
        $call               = Call::factory()->create([
            'created_at' => Carbon::now()->subSeconds($maxTechWaitingTime),
        ]);

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TWILIO_CALL_SID => $call->communication->provider_id,
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_CALL_SID]);
        $message = (new NotExpired(new Exists($call)))->message();
        $request->assertValidationMessages([Lang::get($message, ['attribute' => RequestKeys::TWILIO_CALL_SID])]);
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
            [Call::TWILIO_CALL_STATUS_RINGING],
            [Call::TWILIO_CALL_STATUS_IN_PROGRESS],
            [Call::TWILIO_CALL_STATUS_COMPLETED],
            [Call::TWILIO_CALL_STATUS_BUSY],
            [Call::TWILIO_CALL_STATUS_FAILED],
            [Call::TWILIO_CALL_STATUS_NO_ANSWER],
            [Call::TWILIO_CALL_STATUS_CANCELED],
        ];
    }

    /** @test */
    public function it_requires_a_dial_call_status_parameter()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_DIAL_CALL_STATUS]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::TWILIO_DIAL_CALL_STATUS]),
        ]);
    }

    /** @test */
    public function its_dial_call_status_parameter_must_not_be_outside_of_a_specific_list()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TWILIO_DIAL_CALL_STATUS => 'invalid']);

        $request->assertValidationErrors([RequestKeys::TWILIO_DIAL_CALL_STATUS]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => RequestKeys::TWILIO_DIAL_CALL_STATUS]),
        ]);
    }

    /**
     * @test
     * @dataProvider dialCallStatusProvider
     */
    public function its_dial_call_status_parameter_must_not_be_in_a_specific_list(string $status)
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TWILIO_DIAL_CALL_STATUS => $status]);

        $request->assertValidationErrorsMissing([RequestKeys::TWILIO_DIAL_CALL_STATUS]);
    }

    public function dialCallStatusProvider(): array
    {
        return [
            [Call::TWILIO_DIAL_CALL_STATUS_ANSWERED],
            [Call::TWILIO_DIAL_CALL_STATUS_BUSY],
            [Call::TWILIO_DIAL_CALL_STATUS_CANCELED],
            [Call::TWILIO_DIAL_CALL_STATUS_COMPLETED],
            [Call::TWILIO_DIAL_CALL_STATUS_FAILED],
            [Call::TWILIO_DIAL_CALL_STATUS_NO_ANSWER],
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

    /** @test */
    public function it_returns_the_call()
    {
        $mock = Mockery::mock(Exists::class);
        $mock->shouldReceive('call')->withNoArgs()->once()->andReturn($call = new Call());
        App::bind(Exists::class, function() use ($mock) {
            return $mock;
        });
        $request = StoreRequest::create('', 'POST', []);

        $this->assertSame($call, $request->call());
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
            [Call::TWILIO_CALL_STATUS_QUEUED, Call::STATUS_IN_PROGRESS],
            [Call::TWILIO_CALL_STATUS_RINGING, Call::STATUS_IN_PROGRESS],
            [Call::TWILIO_CALL_STATUS_IN_PROGRESS, Call::STATUS_IN_PROGRESS],
            [Call::TWILIO_CALL_STATUS_COMPLETED, Call::STATUS_COMPLETED],
            [Call::TWILIO_CALL_STATUS_FAILED, Call::STATUS_COMPLETED],
            [Call::TWILIO_CALL_STATUS_NO_ANSWER, Call::STATUS_COMPLETED],
            [Call::TWILIO_CALL_STATUS_CANCELED, Call::STATUS_COMPLETED],
            ['unknown', Call::STATUS_INVALID],
        ];
    }

    /** @test
     * @dataProvider mapDialStatusProvider
     */
    public function it_maps_dial_call_statuses(string $twilioStatus, string $modelStatus)
    {
        $data = [
            RequestKeys::TWILIO_DIAL_CALL_STATUS => $twilioStatus,
        ];

        $request = StoreRequest::create('', 'POST', $data);

        $this->assertEquals($modelStatus, $request->dialStatus());
    }

    public function mapDialStatusProvider(): array
    {
        return [
            [Call::TWILIO_DIAL_CALL_STATUS_ANSWERED, Call::STATUS_IN_PROGRESS],
            [Call::TWILIO_DIAL_CALL_STATUS_BUSY, Call::STATUS_IN_PROGRESS],
            [Call::TWILIO_DIAL_CALL_STATUS_NO_ANSWER, Call::STATUS_IN_PROGRESS],
            [Call::TWILIO_DIAL_CALL_STATUS_COMPLETED, Call::STATUS_COMPLETED],
            [Call::TWILIO_DIAL_CALL_STATUS_FAILED, Call::STATUS_COMPLETED],
            [Call::TWILIO_DIAL_CALL_STATUS_CANCELED, Call::STATUS_COMPLETED],
        ];
    }

    /**
     * @test
     *
     * @dataProvider callEndedProvider
     */
    public function it_knows_if_the_call_ended(string $callStatus, bool $ended)
    {
        /** @var Mockery\Mock|StoreRequest $mock */
        $mock = Mockery::mock(StoreRequest::class)->makePartial();
        $mock->shouldReceive('status')->withNoArgs()->once()->andReturn($callStatus);

        $this->assertEquals($ended, $mock->callEnded());
    }

    public function callEndedProvider(): array
    {
        return [
            [Call::STATUS_IN_PROGRESS, false],
            [Call::STATUS_COMPLETED, true],
            [Call::STATUS_INVALID, false],
        ];
    }

    /**
     * @test
     *
     * @dataProvider agentHungUpProvider
     */
    public function it_knows_if_agent_hung_up(string $callStatus, string $dialStatus, bool $agentHungUp)
    {
        /** @var Mockery\Mock|StoreRequest $mock */
        $mock = Mockery::mock(StoreRequest::class)->makePartial();
        $mock->shouldReceive('status')->withNoArgs()->once()->andReturn($callStatus);
        $mock->shouldReceive('dialStatus')->withNoArgs()->andReturn($dialStatus);

        $this->assertEquals($agentHungUp, $mock->agentHungUp());
    }

    public function agentHungUpProvider(): array
    {
        return [
            [Call::STATUS_IN_PROGRESS, Call::STATUS_IN_PROGRESS, false],
            [Call::STATUS_IN_PROGRESS, Call::STATUS_COMPLETED, true],
            [Call::STATUS_IN_PROGRESS, Call::STATUS_INVALID, false],
            [Call::STATUS_COMPLETED, Call::STATUS_IN_PROGRESS, false],
            [Call::STATUS_COMPLETED, Call::STATUS_COMPLETED, false],
            [Call::STATUS_COMPLETED, Call::STATUS_INVALID, false],
            [Call::STATUS_INVALID, Call::STATUS_IN_PROGRESS, false],
            [Call::STATUS_INVALID, Call::STATUS_COMPLETED, true],
            [Call::STATUS_INVALID, Call::STATUS_INVALID, false],
        ];
    }
}
