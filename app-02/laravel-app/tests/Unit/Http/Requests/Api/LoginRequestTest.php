<?php

namespace Tests\Unit\Http\Requests\Api;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\LoginRequest;
use Illuminate\Foundation\Testing\WithFaker;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

class LoginRequestTest extends RequestTestCase
{
    use WithFaker;

    protected string $requestClass = LoginRequest::class;

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
    public function its_version_is_required_when_device_is_present()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DEVICE => 'a valid device']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::VERSION]);

        $request->assertValidationMessages([
            Lang::get('validation.required_with',
                ['attribute' => RequestKeys::VERSION, 'values' => RequestKeys::DEVICE]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::DEVICE => 'a valid device udid',
            RequestKeys::VERSION => '1.2.3',
        ]);

        $request->assertValidationPassed();
    }
}
