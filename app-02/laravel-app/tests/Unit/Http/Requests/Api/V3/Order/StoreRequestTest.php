<?php

namespace Tests\Unit\Http\Requests\Api\V3\Order;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V3\OrderController;
use App\Http\Requests\Api\V3\Order\StoreRequest;
use App\Models\Item;
use App\Models\Oem;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see OrderController */
class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function its_supplier_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLIER]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::SUPPLIER])]);
    }

    /** @test */
    public function its_supplier_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SUPPLIER => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLIER]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::SUPPLIER])]);
    }

    /** @test */
    public function its_supplier_parameter_must_exist()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SUPPLIER => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SUPPLIER]);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => RequestKeys::SUPPLIER])]);
    }

    /** @test */
    public function its_oem_parameter_can_be_null()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::OEM => null]);

        $request->assertValidationErrorsMissing([RequestKeys::OEM]);
    }

    /** @test */
    public function its_oem_parameter_must_exist_if_not_null()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::OEM => 'invalid_OEM']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::OEM]);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => RequestKeys::OEM])]);
    }

    /** @test */
    public function its_items_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::ITEMS])]);
    }

    /** @test */
    public function its_items_parameter_must_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS]);
        $request->assertValidationMessages([Lang::get('validation.array', ['attribute' => RequestKeys::ITEMS])]);
    }

    /** @test */
    public function each_item_in_items_is_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [[]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0']);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::ITEMS . '.0']),
        ]);
    }

    /** @test */
    public function each_item_uuid_in_items_is_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [[]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.uuid']);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::ITEMS . '.0.uuid']),
        ]);
    }

    /** @test */
    public function each_item_uuid_in_items_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [['uuid' => 1]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.uuid']);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => RequestKeys::ITEMS . '.0.uuid']),
        ]);
    }

    /** @test */
    public function each_item_uuid_in_items_must_exist()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [['uuid' => 'invalid']]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.uuid']);
        $request->assertValidationMessages(['Each item in items must exist.']);
    }

    /** @test */
    public function each_item_quantity_in_items_is_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [[]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.quantity']);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::ITEMS . '.0.quantity']),
        ]);
    }

    /** @test */
    public function each_item_quantity_in_items_must_be_a_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [['quantity' => 'invalid']]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.quantity']);
        $request->assertValidationMessages([
            Lang::get('validation.integer', ['attribute' => RequestKeys::ITEMS . '.0.quantity']),
        ]);
    }

    /** @test */
    public function each_item_quantity_in_items_must_be_minimum_one()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [['quantity' => 0]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.quantity']);
        $request->assertValidationMessages([
            Lang::get('validation.min.numeric', ['attribute' => RequestKeys::ITEMS . '.0.quantity', 'min' => 1]),
        ]);
    }

    /** @test */
    public function it_returns_the_supplier()
    {
        $supplier = Supplier::factory()->createQuietly();
        $request  = StoreRequest::create('', 'POST', [
            RequestKeys::SUPPLIER => $supplier->getRouteKey(),
        ]);

        $this->assertEquals($supplier->getRouteKey(), $request->supplier()->getRouteKey());
    }

    /** @test */
    public function it_returns_the_oem()
    {
        $oem     = Oem::factory()->createQuietly();
        $request = StoreRequest::create('', 'POST', [
            RequestKeys::OEM => $oem->getRouteKey(),
        ]);

        $this->assertEquals($oem->getRouteKey(), $request->oem()->getRouteKey());
    }

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
        $supplier = Supplier::factory()->createQuietly(['zip_code' => '12345']);
        $request  = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE     => $type,
            RequestKeys::SUPPLIER => $supplier->getRouteKey(),
        ]);

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
            [true, OrderDelivery::TYPE_OTHER_DELIVERY],
            [true, OrderDelivery::TYPE_CURRI_DELIVERY],
            [true, OrderDelivery::TYPE_PICKUP],
            [true, OrderDelivery::TYPE_SHIPMENT_DELIVERY],
        ];
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
        $supplier = Supplier::factory()->createQuietly();
        $item     = Item::factory()->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::OEM                   => null,
            RequestKeys::SUPPLIER              => $supplier->getRouteKey(),
            RequestKeys::ITEMS                 => [
                [
                    'uuid'     => $item->getRouteKey(),
                    'quantity' => 1,
                ],
            ],
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
