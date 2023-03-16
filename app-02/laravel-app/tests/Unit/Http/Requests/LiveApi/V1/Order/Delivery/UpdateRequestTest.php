<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Order\Delivery;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\LiveApi\V1\Order\Delivery\UpdateRequest;
use App\Models\CurriDelivery;
use App\Models\ForbiddenZipCode;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Route;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

class UpdateRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = UpdateRequest::class;

    public function setUp(): void
    {
        parent::setUp();

        $this->supplier = Supplier::factory()->createQuietly([
            'timezone' => 'UTC',
            'zip_code' => '11112',
        ]);
        $this->order    = Order::factory()->usingSupplier($this->supplier)->create();
        $orderDelivery  = OrderDelivery::factory()->usingOrder($this->order)->pickup()->create();
        Pickup::factory()->usingOrderDelivery($orderDelivery)->create();
        Route::model(RouteParameters::ORDER, Order::class);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($this->supplier)->create());
    }

    /** @test */
    public function its_type_parameter_is_required()
    {
        $requestKey = RequestKeys::TYPE;
        $request    = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_type_parameter_must_be_a_string()
    {
        $requestKey = RequestKeys::TYPE;
        $request    = $this->formRequest($this->requestClass, [$requestKey => ['invalid']])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /**
     * @test
     * @dataProvider typeProvider
     */
    public function its_type_parameter_should_be_a_valid_value(string $type, bool $valid)
    {
        $requestKey    = RequestKeys::TYPE;
        $order         = Order::factory()->usingSupplier($this->supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $request = $this->formRequest($this->requestClass, [$requestKey => $type])
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    public function typeProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, true],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, true],
            [OrderDelivery::TYPE_PICKUP, true],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, true],
            ['another value', false],
        ];
    }

    /**
     * @test
     * @dataProvider zipCodesProvider
     */
    public function it_denies_when_supplier_has_an_invalid_zip_code_if_the_type_is_curri_delivery(
        string $type,
        ?string $zipCode,
        bool $valid
    ) {
        ForbiddenZipCode::factory()->create(['zip_code' => '11111']);

        Auth::user()->supplier->zip_code = $zipCode;

        $requestKey    = RequestKeys::TYPE;
        $order         = Order::factory()->usingSupplier($this->supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $request = $this->formRequest($this->requestClass, [$requestKey => $type])
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages(['Supplier zip code is invalid or forbidden to book a curri delivery.']);
    }

    public function zipCodesProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, '11111', false],
            [OrderDelivery::TYPE_CURRI_DELIVERY, null, false],
            [OrderDelivery::TYPE_CURRI_DELIVERY, '12345', true],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, null, true],
            [OrderDelivery::TYPE_PICKUP, null, true],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, null, true],
        ];
    }

    /** @test */
    public function its_order_must_have_a_destination_address_if_the_type_parameter_is_curri()
    {
        $requestKey = RequestKeys::TYPE;
        $request    = $this->formRequest($this->requestClass, [$requestKey => OrderDelivery::TYPE_CURRI_DELIVERY])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages(['It cannot change to Curri because destination address does not exist.']);
    }

    /** @test */
    public function its_vehicle_type_parameter_is_required_if_type_is_curri()
    {
        $requestKey = RequestKeys::VEHICLE_TYPE;
        $request    = $this->formRequest($this->requestClass, [RequestKeys::TYPE => OrderDelivery::TYPE_CURRI_DELIVERY])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test
     * @dataProvider typeWithoutCurriProvider
     */
    public function its_vehicle_type_parameter_is_prohibited_unless_type_is_curri(string $type, bool $valid)
    {
        $requestKey = RequestKeys::VEHICLE_TYPE;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => $type,
            $requestKey       => CurriDelivery::VEHICLE_TYPE_CAR,
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.prohibited', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_vehicle_type_parameter_must_be_a_string()
    {
        $requestKey = RequestKeys::VEHICLE_TYPE;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => OrderDelivery::TYPE_CURRI_DELIVERY,
            $requestKey       => ['invalid'],
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /**
     * @test
     * @dataProvider vehicleTypeProvider
     */
    public function its_vehicle_type_parameter_should_be_a_valid_value(string $type, bool $valid)
    {
        $requestKey    = RequestKeys::VEHICLE_TYPE;
        $order         = Order::factory()->usingSupplier($this->supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => OrderDelivery::TYPE_CURRI_DELIVERY,
            $requestKey       => $type,
        ])->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    public function vehicleTypeProvider(): array
    {
        return [
            [CurriDelivery::VEHICLE_TYPE_CAR, true],
            [CurriDelivery::VEHICLE_TYPE_RACK_TRUCK, true],
            ['another vehicle type', false],
        ];
    }

    /** @test
     * @dataProvider feeAllowedTypesProvider
     */
    public function its_fee_parameter_is_required_unless_type_is_pickup_or_curri(string $type, bool $valid)
    {
        $requestKey = RequestKeys::FEE;
        $request    = $this->formRequest($this->requestClass, [RequestKeys::TYPE => $type])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    public function feeAllowedTypesProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, true],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, false],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, false],
            [OrderDelivery::TYPE_PICKUP, true],
        ];
    }

    /** @test
     * @dataProvider feeProhibitedTypesProvider
     */
    public function its_fee_parameter_is_prohibited_if_type_is_pickup_or_curri(string $type, bool $valid)
    {
        $requestKey = RequestKeys::FEE;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => $type,
            $requestKey       => 2000,
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.prohibited', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    public function feeProhibitedTypesProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, false],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, true],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, true],
            [OrderDelivery::TYPE_PICKUP, false],
        ];
    }

    /** @test */
    public function its_fee_parameter_must_be_numeric()
    {
        $requestKey = RequestKeys::FEE;
        $request    = $this->formRequest($this->requestClass, [$requestKey => 'invalid'])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.numeric', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_fee_parameter_must_be_equal_or_greater_than_0()
    {
        $requestKey = RequestKeys::FEE;
        $request    = $this->formRequest($this->requestClass, [$requestKey => -1])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.min.numeric', [
                'attribute' => $this->getDisplayableAttribute($requestKey),
                'min'       => 0,
            ]),
        ]);
    }

    /** @test */
    public function its_fee_parameter_should_have_a_correct_money_format()
    {
        $requestKey = RequestKeys::FEE;
        $request    = $this->formRequest($this->requestClass, [$requestKey => 12.345])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.regex', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_use_store_address_parameter_is_required_if_type_is_curri()
    {
        $requestKey = RequestKeys::USE_STORE_ADDRESS;
        $request    = $this->formRequest($this->requestClass, [RequestKeys::TYPE => OrderDelivery::TYPE_CURRI_DELIVERY])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test
     * @dataProvider typeWithoutCurriProvider
     */
    public function its_use_store_address_parameter_is_prohibited_unless_type_is_curri(string $type, bool $valid)
    {
        $requestKey = RequestKeys::USE_STORE_ADDRESS;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => $type,
            $requestKey       => 1,
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.prohibited', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_use_store_address_parameter_has_to_be_a_boolean()
    {
        $requestKey = RequestKeys::USE_STORE_ADDRESS;
        $request    = $this->formRequest($this->requestClass, [$requestKey => 'invalid'])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.boolean', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_address_parameter_is_required_if_type_is_curri_and_use_store_address_is_false()
    {
        $requestKey = RequestKeys::ADDRESS;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE              => OrderDelivery::TYPE_CURRI_DELIVERY,
            RequestKeys::USE_STORE_ADDRESS => 0,
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test
     * @dataProvider typeWithoutCurriProvider
     */
    public function its_address_parameter_is_prohibited_unless_type_is_curri(string $type, bool $valid)
    {
        $requestKey = RequestKeys::ADDRESS;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => $type,
            $requestKey       => 'value',
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.prohibited', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_address_parameter_is_prohibited_if_use_store_address_is_true()
    {
        $requestKey = RequestKeys::ADDRESS;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 1,
            $requestKey                    => 'value',
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.prohibited', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_address_parameter_must_be_a_string()
    {
        $requestKey = RequestKeys::ADDRESS;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 0,
            $requestKey                    => ['invalid'],
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_address_parameter_must_have_max_255_characters()
    {
        $requestKey = RequestKeys::ADDRESS;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 0,
            $requestKey                    => Str::random(256),
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', [
                'attribute' => $this->getDisplayableAttribute($requestKey),
                'max'       => 255,
            ]),
        ]);
    }

    /** @test */
    public function its_address_2_parameter_must_be_a_string()
    {
        $requestKey = RequestKeys::ADDRESS_2;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 0,
            $requestKey                    => ['invalid'],
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_address_2_parameter_must_have_max_255_characters()
    {
        $requestKey = RequestKeys::ADDRESS_2;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 0,
            $requestKey                    => Str::random(256),
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', [
                'attribute' => $this->getDisplayableAttribute($requestKey),
                'max'       => 255,
            ]),
        ]);
    }

    /** @test */
    public function its_city_parameter_is_required_if_type_is_curri_and_use_store_address_is_false()
    {
        $requestKey = RequestKeys::CITY;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE              => OrderDelivery::TYPE_CURRI_DELIVERY,
            RequestKeys::USE_STORE_ADDRESS => 0,
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test
     * @dataProvider typeWithoutCurriProvider
     */
    public function its_city_parameter_is_prohibited_unless_type_is_curri(string $type, bool $valid)
    {
        $requestKey = RequestKeys::CITY;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => $type,
            $requestKey       => 'value',
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.prohibited', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_city_parameter_is_prohibited_if_use_store_address_is_true()
    {
        $requestKey = RequestKeys::CITY;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 1,
            $requestKey                    => 'value',
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.prohibited', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_city_parameter_must_be_a_string()
    {
        $requestKey = RequestKeys::CITY;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 0,
            $requestKey                    => ['invalid'],
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_city_parameter_must_have_max_255_characters()
    {
        $requestKey = RequestKeys::CITY;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 0,
            $requestKey                    => Str::random(256),
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', [
                'attribute' => $this->getDisplayableAttribute($requestKey),
                'max'       => 255,
            ]),
        ]);
    }

    /** @test */
    public function its_state_parameter_is_required_if_type_is_curri_and_use_store_address_is_false()
    {
        $requestKey = RequestKeys::STATE;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE              => OrderDelivery::TYPE_CURRI_DELIVERY,
            RequestKeys::USE_STORE_ADDRESS => 0,
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test
     * @dataProvider typeWithoutCurriProvider
     */
    public function its_state_parameter_is_prohibited_unless_type_is_curri(string $type, bool $valid)
    {
        $requestKey = RequestKeys::STATE;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => $type,
            $requestKey       => 'value',
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.prohibited', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_state_parameter_is_prohibited_if_use_store_address_is_true()
    {
        $requestKey = RequestKeys::STATE;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 1,
            $requestKey                    => 'value',
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.prohibited', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_state_parameter_must_be_a_string()
    {
        $requestKey = RequestKeys::STATE;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 0,
            $requestKey                    => ['invalid'],
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_state_parameter_must_have_max_255_characters()
    {
        $requestKey = RequestKeys::STATE;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 0,
            $requestKey                    => Str::random(256),
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', [
                'attribute' => $this->getDisplayableAttribute($requestKey),
                'max'       => 255,
            ]),
        ]);
    }

    /** @test */
    public function its_zip_code_parameter_is_required_if_type_is_curri_and_use_store_address_is_false()
    {
        $requestKey = RequestKeys::ZIP_CODE;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE              => OrderDelivery::TYPE_CURRI_DELIVERY,
            RequestKeys::USE_STORE_ADDRESS => 0,
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());
    }

    /** @test
     * @dataProvider typeWithoutCurriProvider
     */
    public function its_zip_code_parameter_is_prohibited_unless_type_is_curri(string $type, bool $valid)
    {
        $requestKey = RequestKeys::ZIP_CODE;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => $type,
            $requestKey       => '12345',
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.prohibited', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_zip_code_parameter_is_prohibited_if_use_store_address_is_true()
    {
        $requestKey = RequestKeys::ZIP_CODE;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 1,
            $requestKey                    => 'value',
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.prohibited', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_zip_code_parameter_must_be_a_string()
    {
        $requestKey = RequestKeys::ZIP_CODE;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 0,
            $requestKey                    => true,
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_zip_code_parameter_should_not_be_more_than_5_digits_long()
    {
        $requestKey = RequestKeys::ZIP_CODE;
        $request    = $this->formRequest($this->requestClass, [$requestKey => Str::random(6)])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $attribute = $this->getDisplayableAttribute($requestKey);
        $request->assertValidationMessages([
            Lang::get('validation.digits', ['attribute' => $attribute, 'digits' => 5]),
        ]);
    }

    /** @test */
    public function its_country_parameter_is_required_if_type_is_curri_and_use_store_address_is_false()
    {
        $requestKey = RequestKeys::COUNTRY;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE              => OrderDelivery::TYPE_CURRI_DELIVERY,
            RequestKeys::USE_STORE_ADDRESS => 0,
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test
     * @dataProvider typeWithoutCurriProvider
     */
    public function its_country_parameter_is_prohibited_unless_type_is_curri(string $type, bool $valid)
    {
        $requestKey = RequestKeys::COUNTRY;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => $type,
            $requestKey       => 'value',
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.prohibited', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    public function typeWithoutCurriProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, true],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, false],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, false],
            [OrderDelivery::TYPE_PICKUP, false],
        ];
    }

    /** @test */
    public function its_country_parameter_is_prohibited_if_use_store_address_is_true()
    {
        $requestKey = RequestKeys::COUNTRY;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 1,
            $requestKey                    => 'value',
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.prohibited', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_country_parameter_must_be_a_string()
    {
        $requestKey = RequestKeys::COUNTRY;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 0,
            $requestKey                    => ['invalid'],
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_country_parameter_must_have_max_255_characters()
    {
        $requestKey = RequestKeys::COUNTRY;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::USE_STORE_ADDRESS => 0,
            $requestKey                    => Str::random(256),
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', [
                'attribute' => $this->getDisplayableAttribute($requestKey),
                'max'       => 255,
            ]),
        ]);
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_passes_on_valid_data(string $type, bool $useStoreAddress = null)
    {
        Carbon::setTestNow('2022-10-11 05:00:00AM');

        $data = [
            RequestKeys::TYPE => $type,
        ];

        if ($type === OrderDelivery::TYPE_CURRI_DELIVERY) {
            $data[RequestKeys::VEHICLE_TYPE] = 'car';
        }

        if ($type !== OrderDelivery::TYPE_PICKUP && $type !== OrderDelivery::TYPE_CURRI_DELIVERY) {
            $data[RequestKeys::FEE] = 123456;
        }

        if (!is_null($useStoreAddress)) {
            $data[RequestKeys::USE_STORE_ADDRESS] = $useStoreAddress;
        }

        if ($type === OrderDelivery::TYPE_CURRI_DELIVERY && !$useStoreAddress) {
            $data[RequestKeys::ADDRESS]   = 'address';
            $data[RequestKeys::ADDRESS_2] = 'address 2';
            $data[RequestKeys::CITY]      = 'city';
            $data[RequestKeys::STATE]     = 'state';
            $data[RequestKeys::ZIP_CODE]  = '12345';
            $data[RequestKeys::COUNTRY]   = 'country';
        }

        $order         = Order::factory()->usingSupplier($this->supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $request = $this->formRequest($this->requestClass, $data)
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

        $request->assertValidationPassed();
    }

    public function dataProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, true],
            [OrderDelivery::TYPE_CURRI_DELIVERY, false],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, null],
            [OrderDelivery::TYPE_PICKUP, null],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, null],
        ];
    }

    /** @test */
    public function it_should_authorize()
    {
        $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey())
            ->assertAuthorized();
    }
}
