<?php

namespace Tests\Unit\Http\Requests\Api\V3\Order\Delivery\Curri\Confirm;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\Api\V3\Order\Delivery\Curri\Confirm\InvokeRequest;
use App\Models\Order;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Lang;
use Route;
use Tests\Unit\Http\Requests\RequestTestCase;

class InvokeRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = InvokeRequest::class;

    public function setUp(): void
    {
        parent::setUp();

        $this->supplier = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $this->order    = Order::factory()->usingSupplier($this->supplier)->create();
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
     * @dataProvider timeProvider
     */
    public function its_start_time_parameter_should_be_a_valid_value(bool $valid, string $startTime, string $endTime)
    {
        $requestKey = RequestKeys::START_TIME;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::START_TIME => $startTime,
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $this->getDisplayableAttribute(RequestKeys::START_TIME)]),
        ]);
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
     * @dataProvider timeProvider
     */
    public function its_end_time_parameter_should_be_a_valid_value(bool $valid, string $startTime, string $endTime)
    {
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
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $this->getDisplayableAttribute(RequestKeys::END_TIME)]),
        ]);
    }

    public function timeProvider(): array
    {
        return [
            [true, Carbon::createFromTime(6)->format('H:i'), Carbon::createFromTime(9)->format('H:i')],
            [true, Carbon::createFromTime(9)->format('H:i'), Carbon::createFromTime(12)->format('H:i')],
            [true, Carbon::createFromTime(12)->format('H:i'), Carbon::createFromTime(15)->format('H:i')],
            [true, Carbon::createFromTime(15)->format('H:i'), Carbon::createFromTime(18)->format('H:i')],
            [false, 'another', 'value'],
        ];
    }

    /**
     * @test
     * @dataProvider timeRangeProvider
     */
    public function its_end_time_parameters_should_form_a_valid_time_range_with_start_time(
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
    public function its_date_and_time_parameters_should_be_more_than_30_minutes_from_now()
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
            Lang::get('The datetime should be after :dateTime.',
                ['dateTime' => Carbon::now()->addMinutes(30)->format('Y-m-d h:iA')]),
        ]);
    }

    /** @test */
    public function it_passes_on_valid_data()
    {
        Carbon::setTestNow('2022-10-11 02:29:00PM');

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::START_TIME => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::END_TIME   => Carbon::createFromTime(15)->format('H:i'),
            RequestKeys::DATE       => '2022-10-11',
        ])->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationPassed();
    }

    /** @test */
    public function it_should_authorize()
    {
        $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey())
            ->assertAuthorized();
    }
}
