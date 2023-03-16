<?php

namespace Tests\Unit\Http\Requests\Api\V4\Order\Delivery;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V4\Order\DeliveryController;
use App\Http\Requests\Api\V4\Order\Delivery\PickupRequest;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use App\Models\SupplierHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;
use URL;

/** @see DeliveryController */
class PickupRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = PickupRequest::class;
    private string   $route;

    public function setUp(): void
    {
        parent::setUp();

        $supplier = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order    = Order::factory()->usingSupplier($supplier)->create();
        $date     = Carbon::createFromFormat('Y-m-d', '2023-02-10');
        SupplierHour::factory()->createQuietly([
            'supplier_id' => $supplier,
            'day'         => strtolower($date->format('l')),
            'from'        => $date->clone()->startOfDay()->format('h:i a'),
            'to'          => $date->clone()->endOfDay()->format('h:i a'),
        ]);
        $this->route = URL::route(RouteNames::API_V4_ORDER_DELIVERY_STORE, [RouteParameters::ORDER => $order]);
    }

    /** @test */
    public function it_should_authorize()
    {
        $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route])->assertAuthorized();
    }

    /** @test */
    public function its_requested_date_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REQUESTED_DATE]);
        $request->assertValidationMessages([
            Lang::get('validation.required',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::REQUESTED_DATE)]),
        ]);
    }

    /** @test */
    public function its_requested_date_parameter_must_have_the_format_Y_m_d()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::REQUESTED_DATE => '12/12/2002'],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REQUESTED_DATE]);
        $request->assertValidationMessages([
            Lang::get('validation.date_format',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::REQUESTED_DATE), 'format' => 'Y-m-d']),
        ]);
    }

    /** @test */
    public function its_requested_start_time_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REQUESTED_START_TIME]);
        $request->assertValidationMessages([
            Lang::get('validation.required',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::REQUESTED_START_TIME)]),
        ]);
    }

    /** @test */
    public function its_requested_start_time_parameter_should_have_H_i_format()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::REQUESTED_START_TIME => 1],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REQUESTED_START_TIME]);
        $request->assertValidationMessages([
            Lang::get('validation.date_format',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::REQUESTED_START_TIME), 'format' => 'H:i']),
        ]);
    }

    /**
     * @test
     * @dataProvider startTimeProvider
     */
    public function its_requested_start_time_parameter_should_be_a_valid_value(bool $valid, string $startTime) {
        $date = '2023-02-10';
        Carbon::setTestNow($date . ' 09:29:00');
        $requestKey = RequestKeys::REQUESTED_START_TIME;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::REQUESTED_DATE       => $date,
            RequestKeys::REQUESTED_START_TIME => $startTime,
        ], ['method' => 'post', 'route' => $this->route]);

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

    public function startTimeProvider(): array
    {
        return[
            [true, '09:00'],
            [true, '17:00'],
            [false, '12:30'],
        ];
    }

    public function timeProvider(): array
    {
        return [
            [true, '09:00', '10:00'],
            [true, '13:00', '14:00'],
            [false, '10:00', '11:01'],
        ];
    }

    /** @test */
    public function its_requested_end_time_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REQUESTED_END_TIME]);
        $request->assertValidationMessages([
            Lang::get('validation.required',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::REQUESTED_END_TIME)]),
        ]);
    }

    /** @test */
    public function its_requested_end_time_parameter_should_have_H_i_format()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::REQUESTED_END_TIME => 1],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REQUESTED_END_TIME]);
        $request->assertValidationMessages([
            Lang::get('validation.date_format',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::REQUESTED_END_TIME), 'format' => 'H:i']),
        ]);
    }

    /**
     * @test
     * @dataProvider timeProvider
     */
    public function its_requested_end_time_parameter_should_be_a_valid_value(
        bool $valid,
        string $startTime,
        string $endTime
    ) {
        $date = '2023-02-10';
        Carbon::setTestNow($date . ' 09:29:00');
        $requestKey = RequestKeys::REQUESTED_END_TIME;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::REQUESTED_DATE       => $date,
            RequestKeys::REQUESTED_START_TIME => $startTime,
            RequestKeys::REQUESTED_END_TIME   => $endTime,
        ], ['method' => 'post', 'route' => $this->route]);

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

    /**
     * @test
     * @dataProvider timeRangeProvider
     */
    public function its_requested_end_time_parameters_should_form_a_valid_time_range_with_requested_start_time(
        bool $valid,
        string $startTime,
        string $endTime
    ) {
        $date = '2023-02-10';
        Carbon::setTestNow($date . ' 09:29:00');
        $requestKey = RequestKeys::REQUESTED_END_TIME;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::REQUESTED_DATE       => $date,
            RequestKeys::REQUESTED_START_TIME => $startTime,
            RequestKeys::REQUESTED_END_TIME   => $endTime,
        ], ['method' => 'post', 'route' => $this->route]);

        if ($valid) {
            $request->assertValidationErrorsMissing([$requestKey]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([$requestKey]);
        $request->assertValidationMessages(["This range of hours is not enabled."]);
    }

    public function timeRangeProvider(): array
    {
        return [
            [true, Carbon::createFromTime(9)->format('H:i'), Carbon::createFromTime(10)->format('H:i')],
            [true, Carbon::createFromTime(10)->format('H:i'), Carbon::createFromTime(11)->format('H:i')],
            [true, Carbon::createFromTime(11)->format('H:i'), Carbon::createFromTime(12)->format('H:i')],
            [true, Carbon::createFromTime(12)->format('H:i'), Carbon::createFromTime(13)->format('H:i')],
            [false, Carbon::createFromTime(13)->format('H:i'), Carbon::createFromTime(15)->format('H:i')],
            [false, Carbon::createFromTime(13)->format('H:i'), Carbon::createFromTime(16)->format('H:i')],
            [false, Carbon::createFromTime(9)->format('H:i'), Carbon::createFromTime(11)->format('H:i')],
        ];
    }

    /** @test */
    public function it_should_not_get_shipment_preference()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::SHIPMENT_PREFERENCE => Carbon::now()->format('Y-m-d'),
        ], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SHIPMENT_PREFERENCE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SHIPMENT_PREFERENCE);
        $request->assertValidationMessages([Lang::get(':attribute is not allowed.', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_pass_on_valid_values()
    {
        $date = '2023-02-10';
        Carbon::setTestNow($date . ' 09:29:00');
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE                 => OrderDelivery::TYPE_PICKUP,
            RequestKeys::REQUESTED_START_TIME => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME   => Carbon::createFromTime(10)->format('H:i'),
            RequestKeys::REQUESTED_DATE       => $date,
        ], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationPassed();
    }
}
