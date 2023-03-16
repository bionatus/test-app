<?php

namespace Tests\Unit\Http\Requests\Api\V2\Twilio\Webhook\Call;

use App;
use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V2\Twilio\Webhook\CallController;
use App\Http\Requests\Api\V2\Twilio\Webhook\Call\StoreRequest;
use App\Http\Requests\Api\V2\Twilio\Webhook\Call\StoreRequestValidationException;
use App\Models\Communication;
use App\Models\Subject;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Application;
use Lang;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see CallController */
class StoreRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function it_requires_a_from_parameter()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_LOWER_FROM]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::TWILIO_LOWER_FROM]),
        ]);
    }

    /** @test */
    public function its_from_parameter_must_be_numeric()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TWILIO_LOWER_FROM => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_LOWER_FROM]);
        $request->assertValidationMessages([
            Lang::get('validation.numeric', ['attribute' => RequestKeys::TWILIO_LOWER_FROM]),
        ]);
    }

    /** @test */
    public function its_from_parameter_must_be_a_user_in_db()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass, [RequestKeys::TWILIO_LOWER_FROM => 0]);

        $request->assertValidationErrors([RequestKeys::TWILIO_LOWER_FROM]);
        $request->assertValidationMessages([
            Lang::get('validation.exists', ['attribute' => RequestKeys::TWILIO_LOWER_FROM]),
        ]);
    }

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
    public function it_requires_a_to_parameter()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TWILIO_LOWER_TO]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::TWILIO_LOWER_TO]),
        ]);
    }

    /** @test */
    public function its_to_parameter_must_be_in_db()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass, [RequestKeys::TWILIO_LOWER_TO => 'invalid']);

        $request->assertValidationErrors([RequestKeys::TWILIO_LOWER_TO]);
        $request->assertValidationMessages([
            Lang::get('validation.exists', ['attribute' => RequestKeys::TWILIO_LOWER_TO]),
        ]);
    }

    /** @test */
    public function it_throws_a_custom_exception_on_validation_failed()
    {
        $this->expectException(StoreRequestValidationException::class);

        $request = StoreRequest::create('', 'POST', []);
        $request->setContainer(App::make(Application::class));
        $request->validateResolved();
    }

    /** @test */
    public function it_throws_exception_when_accessing_the_tech_before_validating()
    {
        $request = new StoreRequest();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Can not access tech before validating the request');

        $request->tech();
    }

    /** @test */
    public function it_throws_exception_when_accessing_the_subject_before_validating()
    {
        $request = new StoreRequest();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Can not access subject before validating the request');

        $request->subject();
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_tech_and_subject_on_validation_passed()
    {
        $this->refreshDatabaseForSingleTest();

        $tech    = User::factory()->create();
        $subject = Subject::factory()->create();

        $request = StoreRequest::create('', 'POST', [
            RequestKeys::TWILIO_LOWER_FROM => $tech->getRouteKey(),
            RequestKeys::TWILIO_LOWER_TO   => $subject->getRouteKey(),
            RequestKeys::TWILIO_CALL_SID   => 'CA123',
        ]);
        $request->setContainer(App::make(Application::class));
        $request->validateResolved();

        $this->assertSame($tech->getKey(), $request->tech()->getKey());
        $this->assertSame($subject->getKey(), $request->subject()->getKey());
    }

    /** @test */
    public function its_provider_is_twilio()
    {
        $request = StoreRequest::create('', 'POST', []);

        $this->assertSame(Communication::PROVIDER_TWILIO, $request->provider());
    }

    /** @test */
    public function its_provider_id_is_the_call_sid_parameter()
    {
        $callSid = 'CA123';
        $request = StoreRequest::create('', 'POST', [
            RequestKeys::TWILIO_CALL_SID => $callSid,
        ]);

        $this->assertSame($callSid, $request->providerId());
    }
}
