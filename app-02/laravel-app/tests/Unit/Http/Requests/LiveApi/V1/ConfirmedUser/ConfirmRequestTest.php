<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\ConfirmedUser;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\ConfirmedUser\ConfirmRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;
use Lang;

class ConfirmRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = ConfirmRequest::class;

    /** @test */
    public function its_customer_tier_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass,
            [$requestKey = RequestKeys::CUSTOMER_TIER => ['array_item']]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_cash_buyer_should_be_a_boolean()
    {
        $request = $this->formRequest($this->requestClass, [$requestKey = RequestKeys::CASH_BUYER => 'a string']);
        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.boolean', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CUSTOMER_TIER => 'preferred',
            RequestKeys::CASH_BUYER    => true,
        ]);

        $request->assertValidationPassed();
    }
}


