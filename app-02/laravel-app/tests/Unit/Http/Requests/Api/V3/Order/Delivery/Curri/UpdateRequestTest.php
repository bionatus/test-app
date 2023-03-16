<?php

namespace Tests\Unit\Http\Requests\Api\V3\Order\Delivery\Curri;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\Api\V3\Order\Delivery\Curri\UpdateRequest;
use App\Models\Order;
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
    private Order    $order;

    public function setUp(): void
    {
        parent::setUp();

        $supplier    = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $this->order = Order::factory()->usingSupplier($supplier)->create();
        Route::model('order', Order::class);
    }

    /** @test */
    public function it_requires_an_address()
    {
        $request = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ADDRESS);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_address_parameter_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ADDRESS => 1])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ADDRESS);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_address_parameter_should_not_be_more_than_255_characters_long()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ADDRESS => Str::random(256)])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ADDRESS);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_address2_parameter_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ADDRESS_2 => 1])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS_2]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ADDRESS_2);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_address2_parameter_should_not_be_more_than_255_characters_long()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ADDRESS_2 => Str::random(256)])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ADDRESS_2]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ADDRESS_2);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_requires_a_country()
    {
        $request = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTRY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COUNTRY);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_country_parameter_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COUNTRY => 1])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTRY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COUNTRY);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_country_parameter_should_not_be_more_than_255_characters_long()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::COUNTRY => Str::random(256)])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::COUNTRY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::COUNTRY);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_requires_a_state()
    {
        $request = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATE);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_state_parameter_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATE => 1])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_state_parameter_should_not_be_more_than_255_characters_long()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATE => Str::random(256)])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATE);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_requires_a_zip_code()
    {
        $request = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ZIP_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ZIP_CODE);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_zip_code_parameter_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ZIP_CODE => 1])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ZIP_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ZIP_CODE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_zip_code_parameter_should_not_be_more_than_5_digits_long()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ZIP_CODE => Str::random(6)])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ZIP_CODE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ZIP_CODE);
        $request->assertValidationMessages([
            Lang::get('validation.digits', ['attribute' => $attribute, 'digits' => 5]),
        ]);
    }

    /** @test */
    public function it_requires_a_city()
    {
        $request = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CITY);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_city_parameter_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CITY => 1])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CITY);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_city_parameter_should_not_be_more_than_255_characters_long()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::CITY => Str::random(256)])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::CITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::CITY);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_note_parameter_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NOTE => 1])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NOTE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NOTE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_note_parameter_should_not_be_more_than_255_characters_long()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NOTE => Str::random(256)])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NOTE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NOTE);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_requires_a_date()
    {
        $request = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DATE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::DATE);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_date_parameter_should_have_Y_m_d_format()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DATE => '12/23/2022'])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DATE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::DATE);
        $request->assertValidationMessages([
            Lang::get('validation.date_format', ['attribute' => $attribute, 'format' => 'Y-m-d']),
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
    public function it_requires_a_start_time()
    {
        $request = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::START_TIME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::START_TIME);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_start_time_parameter_should_have_H_i_format()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::START_TIME => 1])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::START_TIME]);
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
            Lang::get('validation.in', ['attribute' => $this->getDisplayableAttribute(RequestKeys::START_TIME)]),
        ]);
    }

    /** @test */
    public function it_requires_a_end_time()
    {
        $request = $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::END_TIME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::END_TIME);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_end_time_parameter_should_have_H_i_format()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::END_TIME => 1])
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey());

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::START_TIME]);
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
    public function its_date_and_time_parameters_should_be_more_than_30_minutes_from_now()
    {
        Carbon::setTestNow('2022-10-11 02:31:00PM');

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::DATE       => '2022-10-11',
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
            RequestKeys::ADDRESS    => 'fake address',
            RequestKeys::ADDRESS_2  => 'fake address 2',
            RequestKeys::CITY       => 'fake city',
            RequestKeys::STATE      => 'fake state',
            RequestKeys::COUNTRY    => 'fake country',
            RequestKeys::ZIP_CODE   => '12345',
            RequestKeys::NOTE       => 'fake note',
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
