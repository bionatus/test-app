<?php

namespace Tests\Unit\Http\Requests\Api\V2\Twilio\Webhook\Call\Fallback;

use App;
use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V2\Twilio\Webhook\Call\FallbackController;
use App\Http\Requests\Api\V2\Twilio\Webhook\Call\Fallback\StoreRequest;
use App\Http\Requests\Api\V2\Twilio\Webhook\Call\Fallback\StoreRequestValidationException;
use App\Models\Call;
use App\Rules\Call\Exists;
use Illuminate\Foundation\Application;
use Lang;
use Mockery;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see FallbackController */
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
