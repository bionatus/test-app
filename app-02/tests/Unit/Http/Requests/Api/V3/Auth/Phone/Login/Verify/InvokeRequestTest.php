<?php

namespace Tests\Unit\Http\Requests\Api\V3\Auth\Phone\Login\Verify;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Auth\Phone\Login\Verify\InvokeRequest;
use Illuminate\Foundation\Testing\WithFaker;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

class InvokeRequestTest extends RequestTestCase
{
    use WithFaker;

    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function it_requires_a_code()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CODE]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::CODE])]);
    }

    /** @test */
    public function its_code_must_be_an_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CODE => '012345']);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CODE]);
        $request->assertValidationMessages([
            Lang::get('validation.integer', ['attribute' => RequestKeys::CODE]),
        ]);
    }

    /** @test */
    public function its_code_must_be_exactly_six_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CODE => 12345]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CODE]);
        $request->assertValidationMessages([
            Lang::get('validation.digits', ['attribute' => RequestKeys::CODE, 'digits' => 6]),
        ]);
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
    public function it_requires_a_version()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::VERSION]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::VERSION])]);
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
    public function it_should_pass_validation_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CODE    => '123456',
            RequestKeys::DEVICE  => 'a valid device udid',
            RequestKeys::VERSION => '1.2.3',
        ]);

        $request->assertValidationPassed();
    }
}
