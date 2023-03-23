<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Order\InProgress\Delivery\Curri\Price;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\Order\InProgress\Delivery\Curri\Price\InvokeRequest;
use App\Models\CurriDelivery;
use Illuminate\Support\Str;
use Lang;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

class InvokeRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function its_vehicule_type_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::VEHICLE_TYPE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::VEHICLE_TYPE);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_vehicule_type_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::VEHICLE_TYPE => ['an array']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::VEHICLE_TYPE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::VEHICLE_TYPE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /**
     * @test
     * @dataProvider vehicleTypeProvider
     */
    public function its_vehicule_type_parameter_must_be_valid($shouldPass, $vehiculeType)
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::VEHICLE_TYPE => $vehiculeType]);

        if ($shouldPass) {
            $request->assertValidationErrorsMissing([RequestKeys::VEHICLE_TYPE]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::VEHICLE_TYPE]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $this->getDisplayableAttribute(RequestKeys::VEHICLE_TYPE)]),
        ]);
    }

    public function vehicleTypeProvider(): array
    {
        return [
            [false, 'invalid'],
            [true, CurriDelivery::VEHICLE_TYPE_CAR],
            [true, CurriDelivery::VEHICLE_TYPE_RACK_TRUCK],
        ];
    }

    /** @test */
    public function its_use_store_address_parameter_must_be_a_boolean()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::USE_STORE_ADDRESS => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::USE_STORE_ADDRESS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::USE_STORE_ADDRESS);
        $request->assertValidationMessages([Lang::get('validation.boolean', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_address_parameter_is_required_without_use_store_address()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS]);
        $attribute           = $this->getDisplayableAttribute(RequestKeys::ADDRESS);
        $use_store_attribute = $this->getDisplayableAttribute(RequestKeys::USE_STORE_ADDRESS);
        $request->assertValidationMessages([
            Lang::get('validation.required_unless',
                ['attribute' => $attribute, 'other' => $use_store_attribute, 'values' => 1]),
        ]);
    }

    /** @test */
    public function its_address_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ADDRESS => 123]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ADDRESS);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_address_parameter_has_a_maximum_length_of_255_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ADDRESS => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ADDRESS);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_address_2_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ADDRESS_2 => 123]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS_2]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ADDRESS_2);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_address_2_parameter_has_a_maximum_length_of_255_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ADDRESS_2 => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS_2]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ADDRESS_2);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_city_parameter_is_required_without_use_store_address()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CITY]);
        $attribute           = $this->getDisplayableAttribute(RequestKeys::CITY);
        $use_store_attribute = $this->getDisplayableAttribute(RequestKeys::USE_STORE_ADDRESS);
        $request->assertValidationMessages([
            Lang::get('validation.required_unless',
                ['attribute' => $attribute, 'other' => $use_store_attribute, 'values' => 1]),
        ]);
    }

    /** @test */
    public function its_city_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CITY => 123]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CITY);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_city_parameter_has_a_maximum_length_of_255_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CITY => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CITY);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_state_parameter_is_required_without_use_store_address()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATE]);
        $attribute           = $this->getDisplayableAttribute(RequestKeys::STATE);
        $use_store_attribute = $this->getDisplayableAttribute(RequestKeys::USE_STORE_ADDRESS);
        $request->assertValidationMessages([
            Lang::get('validation.required_unless',
                ['attribute' => $attribute, 'other' => $use_store_attribute, 'values' => 1]),
        ]);
    }

    /** @test */
    public function its_state_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATE => 123]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_state_parameter_has_a_maximum_length_of_255_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATE => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATE);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_zip_code_parameter_is_required_without_use_store_address()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ZIP_CODE]);
        $attribute           = $this->getDisplayableAttribute(RequestKeys::ZIP_CODE);
        $use_store_attribute = $this->getDisplayableAttribute(RequestKeys::USE_STORE_ADDRESS);
        $request->assertValidationMessages([
            Lang::get('validation.required_unless',
                ['attribute' => $attribute, 'other' => $use_store_attribute, 'values' => 1]),
        ]);
    }

    /** @test */
    public function its_zip_code_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ZIP_CODE => 123]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ZIP_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ZIP_CODE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_zip_code_parameter_must_be_5_digits()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ZIP_CODE => '123456']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ZIP_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ZIP_CODE);
        $request->assertValidationMessages([
            Lang::get('validation.digits', ['attribute' => $attribute, 'digits' => 5]),
        ]);
    }

    /** @test */
    public function its_country_parameter_is_required_without_use_store_address()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTRY]);
        $attribute           = $this->getDisplayableAttribute(RequestKeys::COUNTRY);
        $use_store_attribute = $this->getDisplayableAttribute(RequestKeys::USE_STORE_ADDRESS);
        $request->assertValidationMessages([
            Lang::get('validation.required_unless',
                ['attribute' => $attribute, 'other' => $use_store_attribute, 'values' => 1]),
        ]);
    }

    /** @test */
    public function its_country_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COUNTRY => 123]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTRY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COUNTRY);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_country_parameter_has_a_maximum_length_of_255_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COUNTRY => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTRY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COUNTRY);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_passes_on_valid_data_with_use_store_addess()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::VEHICLE_TYPE      => 'car',
            RequestKeys::USE_STORE_ADDRESS => 1,
        ]);

        $request->assertValidationPassed();
    }

    /** @test */
    public function it_passes_on_valid_data_without_use_store_address()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::VEHICLE_TYPE      => 'car',
            RequestKeys::USE_STORE_ADDRESS => 0,
            RequestKeys::ADDRESS           => '110 Grace Ave',
            RequestKeys::ADDRESS_2         => 'Building 2',
            RequestKeys::CITY              => 'Aiken',
            RequestKeys::STATE             => 'US-SC',
            RequestKeys::COUNTRY           => 'US',
            RequestKeys::ZIP_CODE          => '12345',
        ]);

        $request->assertValidationPassed();
    }
}
