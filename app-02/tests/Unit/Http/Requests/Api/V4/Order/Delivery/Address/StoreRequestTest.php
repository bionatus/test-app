<?php

namespace Tests\Unit\Http\Requests\Api\V4\Order\Delivery\Address;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V4\Order\Delivery\Address\StoreRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use MenaraSolutions\Geographer\Earth;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see AddressController */
class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function its_destination_address_1_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, []);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_ADDRESS_1]);
        $request->assertValidationMessages([
            Lang::get('validation.required',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_ADDRESS_1)]),
        ]);
    }

    /** @test */
    public function its_destination_address_1_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_ADDRESS_1 => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_ADDRESS_1]);
        $request->assertValidationMessages([
            Lang::get('validation.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_ADDRESS_1)]),
        ]);
    }

    /** @test */
    public function its_destination_address_1_must_be_less_than_256_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_ADDRESS_1 => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_ADDRESS_1]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_ADDRESS_1), 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_destination_address_2_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_ADDRESS_2 => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_ADDRESS_2]);
        $request->assertValidationMessages([
            Lang::get('validation.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_ADDRESS_2)]),
        ]);
    }

    /** @test */
    public function its_destination_address_2_must_be_less_than_256_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_ADDRESS_2 => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_ADDRESS_2]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_ADDRESS_2), 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_destination_country_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, []);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_COUNTRY]);
        $request->assertValidationMessages([
            Lang::get('validation.required',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_COUNTRY)]),
        ]);
    }

    /** @test */
    public function its_destination_country_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_COUNTRY => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_COUNTRY]);
        $request->assertValidationMessages([
            Lang::get('validation.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_COUNTRY)]),
        ]);
    }

    /** @test */
    public function its_company_country_must_be_a_valid_value()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_COUNTRY => 'invalid country']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_COUNTRY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::DESTINATION_COUNTRY);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_destination_city_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, []);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_CITY]);
        $request->assertValidationMessages([
            Lang::get('validation.required',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_CITY)]),
        ]);
    }

    /** @test */
    public function its_destination_city_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_CITY => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_CITY]);
        $request->assertValidationMessages([
            Lang::get('validation.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_CITY)]),
        ]);
    }

    /** @test */
    public function its_destination_city_must_be_less_than_256_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_CITY => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_CITY]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_CITY), 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_destination_state_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, []);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_STATE]);
        $request->assertValidationMessages([
            Lang::get('validation.required',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_STATE)]),
        ]);
    }

    /** @test */
    public function its_destination_state_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_STATE => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_STATE]);
        $request->assertValidationMessages([
            Lang::get('validation.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_STATE)]),
        ]);
    }

    /** @test */
    public function its_company_state_must_be_a_valid_value()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::DESTINATION_COUNTRY => (new Earth())->getCountries()->first()->code,
            RequestKeys::DESTINATION_STATE   => 'invalid',
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_STATE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::DESTINATION_STATE);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_destination_zip_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, []);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_ZIP_CODE]);
        $request->assertValidationMessages([
            Lang::get('validation.required',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_ZIP_CODE)]),
        ]);
    }

    /** @test */
    public function its_destination_zip_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_ZIP_CODE => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_ZIP_CODE]);
        $request->assertValidationMessages([
            Lang::get('validation.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_ZIP_CODE)]),
        ]);
    }

    /** @test */
    public function its_company_zip_code_size_must_be_exactly_5_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_ZIP_CODE => '123456']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_ZIP_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::DESTINATION_ZIP_CODE);
        $request->assertValidationMessages([
            Lang::get('validation.digits', ['attribute' => $attribute, 'digits' => '5']),
        ]);
    }

    /** @test */
    public function its_note_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NOTE => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NOTE]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $this->getDisplayableAttribute(RequestKeys::NOTE)]),
        ]);
    }

    /** @test */
    public function its_note_must_be_less_than_256_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NOTE => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NOTE]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::NOTE), 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_pass_on_valid_values()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::DESTINATION_ADDRESS_1 => 'address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => null,
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'US-AL',
            RequestKeys::DESTINATION_CITY      => 'city',
            RequestKeys::DESTINATION_ZIP_CODE  => '12345',
            RequestKeys::NOTE                  => null,
        ]);

        $request->assertValidationPassed();
    }
}
