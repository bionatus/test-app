<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Order\Fee;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\Order\Fee\StoreRequest;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see FeeController */
class StoreRequestTest extends RequestTestCase
{
    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function its_discount_parameter_can_be_ignored()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationErrorsMissing([RequestKeys::DISCOUNT]);
    }

    /** @test */
    public function its_discount_parameter_must_be_numeric()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DISCOUNT => 'a string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DISCOUNT]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::DISCOUNT);
        $request->assertValidationMessages([Lang::get('validation.numeric', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_discount_parameter_must_be_a_number_not_less_than_0()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DISCOUNT => -1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DISCOUNT]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::DISCOUNT);
        $request->assertValidationMessages([
            Lang::get('validation.min.numeric', [
                'attribute' => $attribute,
                'min'       => 0,
            ]),
        ]);
    }

    /** @test */
    public function its_discount_parameter_should_have_2_decimals_at_most()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DISCOUNT => '12.345']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DISCOUNT]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::DISCOUNT);
        $request->assertValidationMessages([Lang::get('validation.regex', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_tax_parameter_can_be_ignored()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationErrorsMissing([RequestKeys::TAX]);
    }

    /** @test */
    public function its_tax_parameter_must_be_numeric()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TAX => 'a string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAX]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::TAX);
        $request->assertValidationMessages([Lang::get('validation.numeric', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_tax_parameter_must_be_a_number_not_less_than_0()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TAX => -1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAX]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::TAX);
        $request->assertValidationMessages([
            Lang::get('validation.min.numeric', [
                'attribute' => $attribute,
                'min'       => 0,
            ]),
        ]);
    }

    /** @test */
    public function its_tax_parameter_should_have_2_decimals_at_most()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TAX => '12.345']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TAX]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::TAX);
        $request->assertValidationMessages([Lang::get('validation.regex', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_passes_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::DISCOUNT => 123.45,
            RequestKeys::TAX      => 67.89,
        ]);

        $request->assertValidationPassed();
    }
}
