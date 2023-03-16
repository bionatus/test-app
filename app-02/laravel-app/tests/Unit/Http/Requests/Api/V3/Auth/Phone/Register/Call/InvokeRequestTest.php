<?php

namespace Tests\Unit\Http\Requests\Api\V3\Auth\Phone\Register\Call;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Auth\Phone\Register\Call\InvokeRequest;
use App\Models\Phone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Lang;
use Tests\Unit\Http\Requests\FormRequestTest;

class InvokeRequestTest extends FormRequestTest
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
        $attribute = $this->getDisplayableAttribute(RequestKeys::PHONE);
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
    public function its_phone_must_be_a_unique_with_user_not_disabled()
    {
        $user  = User::factory()->create(['disabled_at' => Carbon::now()]);
        $phone = Phone::factory()->usingUser($user)->verified()->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COUNTRY_CODE => $phone->country_code,
            RequestKeys::PHONE        => $phone->number,
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHONE]);
        $request->assertValidationMessages([Lang::get('auth.account_disabled', ['attribute' => RequestKeys::PHONE])]);
    }

    /** @test */
    public function its_phone_must_be_unassigned_or_unverified()
    {
        $user  = User::factory()->create();
        $phone = Phone::factory()->usingUser($user)->verified()->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COUNTRY_CODE => $phone->country_code,
            RequestKeys::PHONE        => $phone->number,
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHONE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PHONE);
        $request->assertValidationMessages([
            Lang::get('validation.unique', ['attribute' => $attribute]),
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

    /** @test */
    public function it_returns_phone()
    {
        $phone            = Phone::factory()->create();
        $existing         = InvokeRequest::create('', 'POST', [
            RequestKeys::COUNTRY_CODE => $phone->country_code,
            RequestKeys::PHONE        => $phone->number,
        ]);
        $validNonExisting = InvokeRequest::create('', 'POST', [
            RequestKeys::COUNTRY_CODE => 1,
            RequestKeys::PHONE        => 5555555555,
        ]);
        $invalid          = InvokeRequest::create('', 'POST', []);

        $this->assertSame($phone->getKey(), $existing->phone()->getKey());
        $this->assertNull($invalid->phone());
        $this->assertNull($validNonExisting->phone());
    }
}
