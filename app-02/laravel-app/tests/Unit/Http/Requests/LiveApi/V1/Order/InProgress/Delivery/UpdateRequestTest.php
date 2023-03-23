<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Order\InProgress\Delivery;

use App\Constants\DeliveryTimeRanges;
use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\LiveApi\V1\Order\InProgress\Delivery\UpdateRequest;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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

        $this->supplier = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $this->order    = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($this->order)->pickup()->create();
        Route::model(RouteParameters::ORDER, Order::class);
    }

    /** @test */
    public function its_date_parameter_is_required()
    {
        $requestKey = RequestKeys::DATE;
        $request    = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_date_parameter_must_have_a_valid_date_format()
    {
        $requestKey = RequestKeys::DATE;
        $request    = $this->formRequest($this->requestClass, [$requestKey => 'invalid'])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.date_format', [
                'attribute' => $this->getDisplayableAttribute($requestKey),
                'format'    => 'Y-m-d',
            ]),
        ]);
    }

    /** @test */
    public function its_date_parameter_must_be_a_date_after_or_equal_to_today()
    {
        $requestKey = RequestKeys::DATE;
        $request    = $this->formRequest($this->requestClass, [$requestKey => '2022-01-01'])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.after_or_equal', [
                'attribute' => $this->getDisplayableAttribute($requestKey),
                'date'      => Carbon::now()->format('Y-m-d'),
            ]),
        ]);
    }

    /** @test */
    public function its_start_time_parameter_is_required()
    {
        $requestKey = RequestKeys::START_TIME;
        $request    = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_start_time_parameter_should_have_H_i_format()
    {
        $requestKey = RequestKeys::START_TIME;
        $request    = $this->formRequest($this->requestClass, [$requestKey => 1])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.date_format',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::START_TIME), 'format' => 'H:i']),
        ]);
    }

    /**
     * @test
     * @dataProvider startTimeProvider
     */
    public function its_start_time_parameter_should_be_a_valid_value(bool $valid, string $time)
    {
        $requestKey = RequestKeys::START_TIME;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::DATE       => Carbon::now()->addDay()->format('Y-m-d'),
            RequestKeys::START_TIME => $time,
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
            Lang::get('The selected start time is invalid.'),
        ]);
    }

    public function startTimeProvider(): array
    {
        return [
            [true, DeliveryTimeRanges::TIME_A],
            [true, DeliveryTimeRanges::TIME_B],
            [true, DeliveryTimeRanges::TIME_C],
            [true, DeliveryTimeRanges::TIME_D],
            [false, Carbon::createFromTime(18)->format('H:i')],
        ];
    }

    /** @test */
    public function its_end_time_parameter_is_required()
    {
        $requestKey = RequestKeys::END_TIME;
        $request    = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function its_end_time_parameter_should_have_H_i_format()
    {
        $requestKey = RequestKeys::END_TIME;
        $request    = $this->formRequest($this->requestClass, [$requestKey => 1])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.date_format',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::END_TIME), 'format' => 'H:i']),
        ]);
    }

    /**
     * @test
     * @dataProvider endTimeProvider
     */
    public function its_end_time_parameter_should_be_a_valid_value(bool $valid, string $startTime, string $endTime)
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::DATE       => Carbon::now()->addDay()->format('Y-m-d'),
            RequestKeys::START_TIME => $startTime,
            RequestKeys::END_TIME   => $endTime,
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([RequestKeys::END_TIME]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::END_TIME]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $this->getDisplayableAttribute(RequestKeys::END_TIME)]),
            Lang::get('The selected start time is invalid.'),
            Lang::get('This range is not enabled.'),
        ]);
    }

    public function endTimeProvider(): array
    {
        return [
            [true, DeliveryTimeRanges::TIME_A, DeliveryTimeRanges::TIME_B],
            [true, DeliveryTimeRanges::TIME_B, DeliveryTimeRanges::TIME_C],
            [true, DeliveryTimeRanges::TIME_C, DeliveryTimeRanges::TIME_D],
            [true, DeliveryTimeRanges::TIME_D, DeliveryTimeRanges::TIME_E],
            [false, Carbon::createFromTime(18)->format('H:i'), Carbon::createFromTime(21)->format('H:i')],
            [false, Carbon::createFromTime(20)->format('H:i'), Carbon::createFromTime(21)->format('H:i')],
        ];
    }

    /**
     * @test
     * @dataProvider timeRangeProvider
     */
    public function its_end_time_parameter_should_form_a_valid_time_range_with_start_time(
        bool $valid,
        string $startTime,
        string $endTime
    ) {
        $requestKey = RequestKeys::END_TIME;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::DATE       => Carbon::now()->addDay()->format('Y-m-d'),
            RequestKeys::START_TIME => $startTime,
            RequestKeys::END_TIME   => $endTime,
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages(["This range is not enabled."]);
    }

    public function timeRangeProvider(): array
    {
        return [
            [true, Carbon::createFromTime(6)->format('H:i'), Carbon::createFromTime(9)->format('H:i')],
            [true, Carbon::createFromTime(9)->format('H:i'), Carbon::createFromTime(12)->format('H:i')],
            [true, Carbon::createFromTime(12)->format('H:i'), Carbon::createFromTime(15)->format('H:i')],
            [true, Carbon::createFromTime(15)->format('H:i'), Carbon::createFromTime(18)->format('H:i')],
            [false, Carbon::createFromTime(6)->format('H:i'), Carbon::createFromTime(12)->format('H:i')],
            [false, Carbon::createFromTime(7)->format('H:i'), Carbon::createFromTime(12)->format('H:i')],
            [false, Carbon::createFromTime(6)->format('H:i'), Carbon::createFromTime(8)->format('H:i')],
            [false, Carbon::createFromTime(7)->format('H:i'), Carbon::createFromTime(8)->format('H:i')],
        ];
    }

    /** @test */
    public function its_date_and_time_parameters_should_be_greater_than_now()
    {
        Carbon::setTestNow('2022-10-11 03:00:00PM');

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::DATE       => Carbon::now()->format('Y-m-d'),
            RequestKeys::START_TIME => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::END_TIME   => Carbon::createFromTime(15)->format('H:i'),
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::END_TIME]);
        $request->assertValidationMessages([
            Lang::get('The datetime should be after :dateTime.', ['dateTime' => Carbon::now()->format('Y-m-d h:iA')]),
        ]);
    }

    /** @test
     * @dataProvider feeAllowedTypesProvider
     */
    public function its_fee_parameter_is_required_unless_type_is_pickup_or_curri(string $type, bool $valid)
    {
        $requestKey = RequestKeys::FEE;
        $order      = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->create(['type' => $type]);

        $request = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

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
        $order      = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->create(['type' => $type]);

        $request = $this->formRequest($this->requestClass, [$requestKey => 2000])
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

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
        $order      = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();

        $request = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

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
        $order      = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->create(['type' => $type]);

        $request = $this->formRequest($this->requestClass, [$requestKey => 1])
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

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
        $order      = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();

        $request = $this->formRequest($this->requestClass, [RequestKeys::USE_STORE_ADDRESS => 0])
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

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
        $order      = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->create(['type' => $type]);

        $request = $this->formRequest($this->requestClass, [$requestKey => 'value'])
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

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
    public function its_city_parameter_is_required_if_type_is_curri_and_use_store_address_is_false()
    {
        $requestKey = RequestKeys::CITY;
        $order      = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();

        $request = $this->formRequest($this->requestClass, [RequestKeys::USE_STORE_ADDRESS => 0])
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

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
        $order      = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->create(['type' => $type]);

        $request = $this->formRequest($this->requestClass, [$requestKey => 'value'])
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

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
        $order      = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();

        $request = $this->formRequest($this->requestClass, [RequestKeys::USE_STORE_ADDRESS => 0])
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

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
        $order      = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->create(['type' => $type]);

        $request = $this->formRequest($this->requestClass, [$requestKey => 'value'])
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

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
        $order      = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();

        $request = $this->formRequest($this->requestClass, [RequestKeys::USE_STORE_ADDRESS => 0])
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

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
        $order      = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->create(['type' => $type]);

        $request = $this->formRequest($this->requestClass, [$requestKey => '12345'])
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

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
        $order      = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();

        $request = $this->formRequest($this->requestClass, [RequestKeys::USE_STORE_ADDRESS => 0])
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

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
        $order      = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->create(['type' => $type]);

        $request = $this->formRequest($this->requestClass, [$requestKey => 'value'])
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

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
     * @dataProvider typeProvider
     */
    public function it_passes_on_valid_data(string $type, bool $useStoreAddress = null)
    {
        Carbon::setTestNow('2022-10-11 05:00:00AM');

        $data = [
            RequestKeys::DATE       => Carbon::now()->format('Y-m-d'),
            RequestKeys::START_TIME => Carbon::createFromTime(6)->format('H:i'),
            RequestKeys::END_TIME   => Carbon::createFromTime(9)->format('H:i'),
        ];

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

        $order = Order::factory()->usingSupplier($this->supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->create(['type' => $type]);

        $request = $this->formRequest($this->requestClass, $data)
            ->addRouteParameter(RouteParameters::ORDER, $order->getRouteKey());

        $request->assertValidationPassed();
    }

    public function typeProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, true],
            [OrderDelivery::TYPE_CURRI_DELIVERY, false],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY],
            [OrderDelivery::TYPE_PICKUP],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY],
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
