<?php

namespace Tests\Unit\Http\Requests\Api\V3\Order\Delivery;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V3\Order\DeliveryController;
use App\Http\Requests\Api\V3\Order\Delivery\UpdateRequest;
use App\Models\OrderDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see DeliveryController */
class UpdateRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = UpdateRequest::class;

    /** @test */
    public function its_type_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute(RequestKeys::TYPE)]),
        ]);
    }

    /** @test */
    public function its_type_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TYPE => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $this->getDisplayableAttribute(RequestKeys::TYPE)]),
        ]);
    }

    /**
     * @test
     * @dataProvider typeProvider
     */
    public function its_type_must_be_valid($shouldPass, $type)
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TYPE => $type]);

        if ($shouldPass) {
            $request->assertValidationErrorsMissing([RequestKeys::TYPE]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $this->getDisplayableAttribute(RequestKeys::TYPE)]),
        ]);
    }

    public function typeProvider(): array
    {
        return [
            [false, 'invalid'],
            [true, OrderDelivery::TYPE_WAREHOUSE_DELIVERY],
            [true, OrderDelivery::TYPE_CURRI_DELIVERY],
            [true, OrderDelivery::TYPE_PICKUP],
            [true, OrderDelivery::TYPE_SHIPMENT_DELIVERY],
        ];
    }

    /** @test */
    public function its_requested_date_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);

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
        $request = $this->formRequest($this->requestClass, [RequestKeys::REQUESTED_DATE => '12/12/2002']);

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
        $request = $this->formRequest($this->requestClass);

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
        $request = $this->formRequest($this->requestClass, [RequestKeys::REQUESTED_START_TIME => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REQUESTED_START_TIME]);
        $request->assertValidationMessages([
            Lang::get('validation.date_format',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::REQUESTED_START_TIME), 'format' => 'H:i']),
        ]);
    }

    /**
     * @test
     * @dataProvider timeProvider
     */
    public function its_requested_start_time_parameter_should_be_a_valid_value(
        bool $valid,
        string $startTime,
        string $endTime
    ) {
        $requestKey = RequestKeys::REQUESTED_START_TIME;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::DATE                 => Carbon::now()->addDay()->format('Y-m-d'),
            RequestKeys::REQUESTED_START_TIME => $startTime,
            RequestKeys::REQUESTED_END_TIME   => $endTime,
        ]);

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

    /** @test */
    public function its_requested_end_time_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);

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
        $request = $this->formRequest($this->requestClass, [RequestKeys::REQUESTED_END_TIME => 1]);

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
        $requestKey = RequestKeys::REQUESTED_END_TIME;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::DATE                 => Carbon::now()->addDay()->format('Y-m-d'),
            RequestKeys::REQUESTED_START_TIME => $startTime,
            RequestKeys::REQUESTED_END_TIME   => $endTime,
        ]);

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
        $requestKey = RequestKeys::REQUESTED_END_TIME;
        $request    = $this->formRequest($this->requestClass, [
            RequestKeys::DATE                 => Carbon::now()->addDay()->format('Y-m-d'),
            RequestKeys::REQUESTED_START_TIME => $startTime,
            RequestKeys::REQUESTED_END_TIME   => $endTime,
        ]);

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
    public function its_destination_address_1_parameter_is_required_unless_type_is_pickup()
    {
        $failedRequest = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => 'not pickup',
        ]);

        $failedRequest->assertValidationFailed();
        $failedRequest->assertValidationErrors([RequestKeys::DESTINATION_ADDRESS_1]);
        $failedRequest->assertValidationMessages([
            Lang::get('validation.required_unless', [
                'attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_ADDRESS_1),
                'other'     => $this->getDisplayableAttribute(RequestKeys::TYPE),
                'values'    => OrderDelivery::TYPE_PICKUP,
            ]),
        ]);

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => OrderDelivery::TYPE_PICKUP,
        ]);
        $request->assertValidationErrorsMissing([RequestKeys::DESTINATION_ADDRESS_1]);
    }

    /** @test */
    public function its_destination_address_1_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_ADDRESS_1 => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_ADDRESS_1]);
        $request->assertValidationMessages([
            Lang::get('validation.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_ADDRESS_1)]),
        ]);
    }

    /** @test */
    public function its_destination_address_1_must_be_less_than_256_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_ADDRESS_1 => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_ADDRESS_1]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_ADDRESS_1), 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_destination_address_2_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_ADDRESS_2 => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_ADDRESS_2]);
        $request->assertValidationMessages([
            Lang::get('validation.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_ADDRESS_2)]),
        ]);
    }

    /** @test */
    public function its_destination_address_2_must_be_less_than_256_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_ADDRESS_2 => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_ADDRESS_2]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_ADDRESS_2), 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_destination_country_parameter_is_required_unless_type_is_pickup()
    {
        $failedRequest = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => 'not pickup',
        ]);

        $failedRequest->assertValidationFailed();
        $failedRequest->assertValidationErrors([RequestKeys::DESTINATION_COUNTRY]);
        $failedRequest->assertValidationMessages([
            Lang::get('validation.required_unless', [
                'attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_COUNTRY),
                'other'     => $this->getDisplayableAttribute(RequestKeys::TYPE),
                'values'    => OrderDelivery::TYPE_PICKUP,
            ]),
        ]);

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => OrderDelivery::TYPE_PICKUP,
        ]);
        $request->assertValidationErrorsMissing([RequestKeys::DESTINATION_COUNTRY]);
    }

    /** @test */
    public function its_destination_country_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_COUNTRY => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_COUNTRY]);
        $request->assertValidationMessages([
            Lang::get('validation.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_COUNTRY)]),
        ]);
    }

    /** @test */
    public function its_destination_country_must_be_less_than_256_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_COUNTRY => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_COUNTRY]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_COUNTRY), 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_destination_city_parameter_is_required_unless_type_is_pickup()
    {
        $failedRequest = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => 'not pickup',
        ]);

        $failedRequest->assertValidationFailed();
        $failedRequest->assertValidationErrors([RequestKeys::DESTINATION_CITY]);
        $failedRequest->assertValidationMessages([
            Lang::get('validation.required_unless', [
                'attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_CITY),
                'other'     => $this->getDisplayableAttribute(RequestKeys::TYPE),
                'values'    => OrderDelivery::TYPE_PICKUP,
            ]),
        ]);

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => OrderDelivery::TYPE_PICKUP,
        ]);
        $request->assertValidationErrorsMissing([RequestKeys::DESTINATION_CITY]);
    }

    /** @test */
    public function its_destination_city_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_CITY => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_CITY]);
        $request->assertValidationMessages([
            Lang::get('validation.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_CITY)]),
        ]);
    }

    /** @test */
    public function its_destination_city_must_be_less_than_256_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_CITY => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_CITY]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_CITY), 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_destination_state_parameter_is_required_unless_type_is_pickup()
    {
        $failedRequest = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => 'not pickup',
        ]);

        $failedRequest->assertValidationFailed();
        $failedRequest->assertValidationErrors([RequestKeys::DESTINATION_STATE]);
        $failedRequest->assertValidationMessages([
            Lang::get('validation.required_unless', [
                'attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_STATE),
                'other'     => $this->getDisplayableAttribute(RequestKeys::TYPE),
                'values'    => OrderDelivery::TYPE_PICKUP,
            ]),
        ]);

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => OrderDelivery::TYPE_PICKUP,
        ]);
        $request->assertValidationErrorsMissing([RequestKeys::DESTINATION_STATE]);
    }

    /** @test */
    public function its_destination_state_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_STATE => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_STATE]);
        $request->assertValidationMessages([
            Lang::get('validation.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_STATE)]),
        ]);
    }

    /** @test */
    public function its_destination_state_must_be_less_than_256_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_STATE => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_STATE]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_STATE), 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_destination_zip_parameter_is_required_unless_type_is_pickup()
    {
        $failedRequest = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => 'not pickup',
        ]);

        $failedRequest->assertValidationFailed();
        $failedRequest->assertValidationErrors([RequestKeys::DESTINATION_ZIP_CODE]);
        $failedRequest->assertValidationMessages([
            Lang::get('validation.required_unless', [
                'attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_ZIP_CODE),
                'other'     => $this->getDisplayableAttribute(RequestKeys::TYPE),
                'values'    => OrderDelivery::TYPE_PICKUP,
            ]),
        ]);

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE => OrderDelivery::TYPE_PICKUP,
        ]);
        $request->assertValidationErrorsMissing([RequestKeys::DESTINATION_ZIP_CODE]);
    }

    /** @test */
    public function its_destination_zip_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_ZIP_CODE => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_ZIP_CODE]);
        $request->assertValidationMessages([
            Lang::get('validation.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_ZIP_CODE)]),
        ]);
    }

    /** @test */
    public function its_destination_zip_must_be_less_than_256_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DESTINATION_ZIP_CODE => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DESTINATION_ZIP_CODE]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::DESTINATION_ZIP_CODE), 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_note_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NOTE => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NOTE]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $this->getDisplayableAttribute(RequestKeys::NOTE)]),
        ]);
    }

    /** @test */
    public function its_note_must_be_less_than_256_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NOTE => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NOTE]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::NOTE), 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_pass_on_valid_values()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE                  => 'warehouse_delivery',
            RequestKeys::REQUESTED_START_TIME  => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-11-04',
            RequestKeys::DESTINATION_ADDRESS_1 => 'address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => null,
            RequestKeys::DESTINATION_COUNTRY   => 'country',
            RequestKeys::DESTINATION_STATE     => 'state',
            RequestKeys::DESTINATION_CITY      => 'city',
            RequestKeys::DESTINATION_ZIP_CODE  => '12345',
            RequestKeys::NOTE                  => null,
        ]);

        $request->assertValidationPassed();
    }
}
