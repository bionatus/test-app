<?php

namespace Tests\Unit\Http\Requests\Api\V2\PushNotificationToken;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V2\PushNotificationToken\StoreRequest;
use App\Models\PushNotificationToken;
use Illuminate\Foundation\Testing\WithFaker;
use Lang;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

class StoreRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;
    use WithFaker;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function it_requires_an_os()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::OS]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::OS])]);
    }

    /** @test */
    public function its_os_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::OS => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::OS]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::OS])]);
    }

    /** @test */
    public function it_os_must_be_valid()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::OS => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::OS]);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => RequestKeys::OS])]);
    }

    /** @test */
    public function it_requires_a_device()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DEVICE]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::DEVICE])]);
    }

    /** @test */
    public function its_device_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DEVICE => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DEVICE]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::DEVICE])]);
    }

    /** @test */
    public function its_device_must_have_at_least_10_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DEVICE => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DEVICE]);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', [
                'attribute' => RequestKeys::DEVICE,
                'min'       => 10,
            ]),
        ]);
    }

    /** @test */
    public function its_device_must_have_less_than_256_characters()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::DEVICE => $this->faker->regexify('[a-zA-Z0-9]{256}')]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DEVICE]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', [
                'attribute' => RequestKeys::DEVICE,
                'max'       => 255,
            ]),
        ]);
    }

    /** @test */
    public function its_version_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::VERSION => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::VERSION]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::VERSION])]);
    }

    /** @test */
    public function its_version_must_be_a_valid_version_number()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::VERSION => 'any string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::VERSION]);
        $request->assertValidationMessages([Lang::get('validation.regex', ['attribute' => RequestKeys::VERSION])]);
    }

    /** @test */
    public function it_requires_an_token()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TOKEN]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::TOKEN])]);
    }

    /** @test */
    public function its_token_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TOKEN => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TOKEN]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::TOKEN])]);
    }

    /** @test */
    public function its_token_must_have_at_least_10_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TOKEN => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TOKEN]);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', [
                'attribute' => RequestKeys::TOKEN,
                'min'       => 10,
            ]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $this->refreshDatabaseForSingleTest();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::OS      => PushNotificationToken::OS_ANDROID,
            RequestKeys::DEVICE  => 'a valid device udid',
            RequestKeys::VERSION => '1.2.3',
            RequestKeys::TOKEN   => 'a valid token',
        ]);

        $request->assertValidationPassed();
    }
}
