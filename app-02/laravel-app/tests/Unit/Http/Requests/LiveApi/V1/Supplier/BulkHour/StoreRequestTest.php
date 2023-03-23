<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Supplier\BulkHour;

use App\Constants\RequestKeys;
use App\Http\Controllers\LiveApi\V1\Supplier\BulkHourController;
use App\Http\Requests\LiveApi\V1\Supplier\BulkHour\StoreRequest;
use Lang;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see BulkHourController */
class StoreRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function its_hours_parameter_must_be_present()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::HOURS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::HOURS);
        $request->assertValidationMessages([Lang::get('validation.present', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_hours_parameter_must_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::HOURS => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::HOURS]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::HOURS);
        $request->assertValidationMessages([Lang::get('validation.array', ['attribute' => $attribute])]);
    }

    /** @test */
    public function each_item_in_its_hours_parameter_must_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::HOURS => ['just a string']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$attribute = RequestKeys::HOURS . '.0']);
        $request->assertValidationMessages([Lang::get('validation.array', ['attribute' => $attribute])]);
    }

    /** @test */
    public function each_item_in_its_hours_parameter_must_have_a_day()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::HOURS => [['any data']]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$attribute = RequestKeys::HOURS . '.0.day']);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function each_hours_day_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::HOURS => [['day' => ['invalid']]]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$attribute = RequestKeys::HOURS . '.0.day']);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function each_hours_day_must_be_valid()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::HOURS => [['day' => 'invalid']]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$attribute = RequestKeys::HOURS . '.0.day']);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => $attribute])]);
    }

    /** @test */
    public function each_item_in_its_hours_parameter_must_have_a_from_parameter()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::HOURS => [['any data']]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$attribute = RequestKeys::HOURS . '.0.from']);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function each_hours_from_parameter_must_be_a_date_format_time()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::HOURS => [['from' => 'invalid time']]]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([$attribute = RequestKeys::HOURS . '.0.from']);
        $request->assertValidationMessages([
            Lang::get('validation.date_format', ['attribute' => $attribute, 'format' => 'H:i']),
        ]);
    }

    /** @test */
    public function each_item_in_its_hours_parameter_must_have_a_to_parameter()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::HOURS => [['any data']]]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([$attribute = RequestKeys::HOURS . '.0.to']);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function each_hours_to_parameter_must_be_a_date_format_time()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::HOURS => [['to' => 'invalid time']]]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([$attribute = RequestKeys::HOURS . '.0.to']);
        $request->assertValidationMessages([
            Lang::get('validation.date_format', ['attribute' => $attribute, 'format' => 'H:i']),
        ]);
    }

    /** @test
     * @dataProvider timesIncorrectFromAfterToProvider
     */
    public function each_hours_to_parameter_must_be_after_from_in_format_time(array $dayTime)
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::HOURS => [$dayTime],
        ]);
        $request->assertValidationFailed();
        $request->assertValidationErrors([$attribute = RequestKeys::HOURS . '.0.to']);
        $request->assertValidationMessages([
            Lang::get('validation.after', ['attribute' => $attribute, 'date' => RequestKeys::HOURS . '.0.from']),
        ]);
    }

    public function timesIncorrectFromAfterToProvider(): array
    {
        return [
            [['day' => 'monday', 'from' => '21:00', 'to' => '17:00']],
            [['day' => 'tuesday', 'from' => '21:00', 'to' => '17:00']],
            [['day' => 'wednesday', 'from' => '21:00', 'to' => '17:00']],
            [['day' => 'thursday', 'from' => '21:00', 'to' => '17:00']],
            [['day' => 'friday', 'from' => '21:00', 'to' => '17:00']],
            [['day' => 'saturday', 'from' => '21:00', 'to' => '17:00']],
            [['day' => 'sunday', 'from' => '21:00', 'to' => '17:00']],
        ];
    }

    /** @test */
    public function it_pass_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::HOURS => [
                ['day' => 'monday', 'from' => '09:00', 'to' => '17:00'],
                ['day' => 'tuesday', 'from' => '09:00', 'to' => '17:00'],
                ['day' => 'wednesday', 'from' => '09:00', 'to' => '17:00'],
                ['day' => 'thursday', 'from' => '09:00', 'to' => '17:00'],
                ['day' => 'friday', 'from' => '09:00', 'to' => '17:00'],
                ['day' => 'saturday', 'from' => '09:00', 'to' => '17:00'],
                ['day' => 'sunday', 'from' => '09:00', 'to' => '17:00'],
            ],
        ]);

        $request->assertValidationPassed();
    }
}
