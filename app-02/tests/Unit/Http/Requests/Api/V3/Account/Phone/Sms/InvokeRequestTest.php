<?php

namespace Tests\Unit\Http\Requests\Api\V3\Account\Phone\Sms;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Account\Phone\Sms\InvokeRequest;
use App\Models\Phone;
use App\Rules\Phone\SmsAvailable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Lang;
use Mockery;
use Tests\Unit\Http\Requests\RequestTestCase;

class InvokeRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function it_requires_a_country_code()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTRY_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COUNTRY_CODE);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_country_code_must_be_valid()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COUNTRY_CODE => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTRY_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COUNTRY_CODE);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function it_requires_a_phone()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COUNTRY_CODE);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_phone_must_be_an_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PHONE => 'not integer']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PHONE);
        $request->assertValidationMessages([
            Lang::get('validation.integer', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_phone_size_must_be_at_least_7_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PHONE => 123456]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PHONE);
        $request->assertValidationMessages([
            Lang::get('validation.digits_between', ['attribute' => $attribute, 'min' => 7, 'max' => 15]),
        ]);
    }

    /** @test */
    public function its_phone_size_must_be_at_most_15_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PHONE => 1234567890123456]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PHONE);
        $request->assertValidationMessages([
            Lang::get('validation.digits_between', ['attribute' => $attribute, 'min' => 7, 'max' => 15]),
        ]);
    }

    /** @test */
    public function its_phone_full_number_must_be_unique_among_verified_ones()
    {
        $unique = Phone::factory()->create([
            'verified_at' => Carbon::now(),
        ]);

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COUNTRY_CODE => $unique->country_code,
            RequestKeys::PHONE        => $unique->number,
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHONE]);
        $request->assertValidationMessages([
            Lang::get('validation.unique', ['attribute' => RequestKeys::PHONE]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COUNTRY_CODE => 1,
            RequestKeys::PHONE        => 1234567,
        ]);

        $request->assertValidationPassed();
    }

    /** @test
     * @dataProvider PhoneProvider
     */
    public function it_returns_the_phone(?Phone $phone)
    {
        $rule = Mockery::mock(SmsAvailable::class);
        $rule->shouldReceive('phone')->withNoArgs()->once()->andReturn($phone);
        App::bind(SmsAvailable::class, fn() => $rule);

        $request = InvokeRequest::create('', 'POST', []);

        $this->assertSame($phone, $request->phone());
    }

    public function phoneProvider(): array
    {
        return [
            [null],
            [new Phone()],
        ];
    }
}
