<?php

namespace Tests\Unit\Http\Requests\Api\V4\Order\ConfirmTotal;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V4\Order\ConfirmTotalController;
use App\Http\Requests\Api\V4\Order\ConfirmTotal\StoreRequest;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;
use URL;

/** @see ConfirmTotalController */
class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = StoreRequest::class;
    private string   $route;

    protected function setUp(): void
    {
        parent::setUp();
        $order = Order::factory()->createQuietly();

        $this->route = URL::route(RouteNames::API_V4_ORDER_CONFIRM_TOTAL_STORE, ['order' => $order]);
    }

    /** @test */
    public function its_paid_total_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PAID_TOTAL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PAID_TOTAL);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_paid_total_parameter_must_be_a_numeric()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PAID_TOTAL => 'da1234']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PAID_TOTAL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PAID_TOTAL);
        $request->assertValidationMessages([Lang::get('validation.numeric', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_paid_total_parameter_must_be_equal_or_greater_than_0()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PAID_TOTAL => -1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PAID_TOTAL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PAID_TOTAL);
        $request->assertValidationMessages([
            Lang::get('validation.min.numeric', [
                'attribute' => $attribute,
                'min'       => 0,
            ]),
        ]);
    }

    /** @test */
    public function its_paid_total_parameter_must_be_equal_or_less_than_999999()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::PAID_TOTAL => 1000000]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::PAID_TOTAL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::PAID_TOTAL);
        $request->assertValidationMessages([
            Lang::get('validation.max.numeric', [
                'attribute' => $attribute,
                'max'       => 999999,
            ]),
        ]);
    }
}
