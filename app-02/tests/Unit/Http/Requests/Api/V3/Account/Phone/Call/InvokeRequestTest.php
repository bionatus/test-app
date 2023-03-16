<?php

namespace Tests\Unit\Http\Requests\Api\V3\Account\Phone\Call;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Account\Phone\Call\InvokeRequest;
use App\Models\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Lang;
use Str;
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
        $attribute = Str::of(RequestKeys::COUNTRY_CODE)->replace('_', ' ');
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
        $attribute = Str::of(RequestKeys::COUNTRY_CODE)->replace('_', ' ');
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
        $attribute = Str::of(RequestKeys::PHONE)->replace('_', ' ');
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
        $attribute = Str::of(RequestKeys::PHONE)->replace('_', ' ');
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
        $attribute = Str::of(RequestKeys::PHONE)->replace('_', ' ');
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
        $attribute = Str::of(RequestKeys::PHONE)->replace('_', ' ');
        $request->assertValidationMessages([
            Lang::get('validation.digits_between', ['attribute' => $attribute, 'min' => 7, 'max' => 15]),
        ]);
    }

    /** @test */
    public function its_phone_full_number_must_be_unique_among_unverified_ones()
    {
        $unique    = Phone::factory()->create([
            'verified_at' => Carbon::now(),
        ]);
        $nonUnique = Phone::factory()->create();
        $request   = $this->formRequest($this->requestClass, [
            RequestKeys::COUNTRY_CODE => $unique->country_code,
            RequestKeys::PHONE        => $unique->number,
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PHONE]);
        $attribute = Str::of(RequestKeys::PHONE)->replace('_', ' ');
        $request->assertValidationMessages([
            Lang::get('validation.unique', ['attribute' => $attribute]),
        ]);

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::COUNTRY_CODE => $nonUnique->country_code,
            RequestKeys::PHONE        => $nonUnique->number,
        ]);

        $request->assertValidationPassed();
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
