<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\ConfirmedUser;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\ConfirmedUser\UpdateRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

class UpdateRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = UpdateRequest::class;

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
    public function its_cash_buyer_is_required()
    {
        $request = $this->formRequest($this->requestClass);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CASH_BUYER]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute(RequestKeys::CASH_BUYER)]),
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

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_should_pass_validation_on_valid_data(?string $customerTier, bool $cashBuyer)
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::CUSTOMER_TIER => $customerTier,
            RequestKeys::CASH_BUYER    => $cashBuyer,
        ]);

        $request->assertValidationPassed();
    }

    public function dataProvider()
    {
        return [
            [null, true],
            ['test tier', true],
        ];
    }
}
