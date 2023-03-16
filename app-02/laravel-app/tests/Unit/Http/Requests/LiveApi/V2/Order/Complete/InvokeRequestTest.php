<?php

namespace Tests\Unit\Http\Requests\LiveApi\V2\Order\Complete;

use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V2\Order\CompleteController;
use App\Http\Requests\LiveApi\V2\Order\Complete\InvokeRequest;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderStaff;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;
use URL;

/** @see CompleteController */
class InvokeRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = InvokeRequest::class;

    public function setUp(): void
    {
        parent::setUp();

        $staff       = Staff::factory()->createQuietly(['name' => 'Example']);
        $supplier    = $staff->supplier;
        $this->order = Order::factory()->usingSupplier($supplier)->approved()->create();
        OrderStaff::factory()->usingStaff($staff)->usingOrder($this->order)->create();
        OrderDelivery::factory()->usingOrder($this->order)->create([
            'type' => OrderDelivery::TYPE_PICKUP,
        ]);
        $this->order->loadMissing(['orderDelivery']);
        $this->route = URL::route(LiveApiV2::LIVE_API_V2_ORDER_COMPLETE_STORE,
            [RouteParameters::ORDER => $this->order]);
    }

    /** @test */
    public function it_should_authorize()
    {
        $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route])->assertAuthorized();
    }

    /**
     * @test
     * @dataProvider deliveryTypeProvider
     */
    public function its_total_parameter_is_required_if_delivery_type_is_pickup(string $deliveryType, bool $required)
    {
        $orderDelivery = $this->order->orderDelivery;
        $orderDelivery->type = $deliveryType;
        $orderDelivery->save();

        $request = $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route]);

        if (!$required){
            $request->assertValidationPassed();
            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TOTAL]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::TOTAL]),
        ]);
    }

    public function deliveryTypeProvider(): array
    {
        return [
            [OrderDelivery::TYPE_PICKUP, true],
            [OrderDelivery::TYPE_CURRI_DELIVERY, false],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, false],
        ];
    }

    /** @test */
    public function its_total_parameter_must_be_numeric()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TOTAL => 'a string'],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TOTAL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::TOTAL);
        $request->assertValidationMessages([Lang::get('validation.numeric', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_total_parameter_must_be_a_number_not_less_than_0()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TOTAL => -1],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TOTAL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::TOTAL);
        $request->assertValidationMessages([
            Lang::get('validation.min.numeric', [
                'attribute' => $attribute,
                'min'       => 0,
            ]),
        ]);
    }

    /** @test */
    public function its_total_parameter_should_have_money_format()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TOTAL => '12.345'],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TOTAL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::TOTAL);
        $request->assertValidationMessages([Lang::get('validation.regex', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_passes_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TOTAL => 67.89,
        ], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationPassed();
    }
}
