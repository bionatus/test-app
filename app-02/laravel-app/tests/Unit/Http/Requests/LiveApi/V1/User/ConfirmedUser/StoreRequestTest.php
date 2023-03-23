<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\User\ConfirmedUser;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\User\SupplierUser\StoreRequest;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

class StoreRequestTest extends RequestTestCase
{
    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function it_requires_cash_buyer()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CASH_BUYER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CASH_BUYER);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_cash_buyer_must_be_boolean()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CASH_BUYER => 'not boolean']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CASH_BUYER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CASH_BUYER);
        $request->assertValidationMessages([Lang::get('validation.boolean', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_customer_tier_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CUSTOMER_TIER => ['not string']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CUSTOMER_TIER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CUSTOMER_TIER);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_customer_tier_must_be_less_than_256_characters_long()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CUSTOMER_TIER => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CUSTOMER_TIER]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CUSTOMER_TIER);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CASH_BUYER    => true,
            RequestKeys::CUSTOMER_TIER => 'a string',
        ]);

        $request->assertValidationPassed();
    }
}
