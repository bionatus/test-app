<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\InternalNotification\Supplier;

use App\Constants\InternalNotificationsSourceEvents;
use App\Constants\RequestKeys;
use App\Http\Controllers\LiveApi\V1\InternalNotification\Supplier\SendController;
use App\Http\Requests\LiveApi\V1\InternalNotification\Supplier\InvokeRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Lang;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see SendController */
class InvokeRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;
    use WithFaker;

    protected string $requestClass = InvokeRequest::class;

    /** @test */
    public function its_data_parameter_must_be_an_array()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::DATA => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::DATA]);
        $request->assertValidationMessages([Lang::get('validation.array', ['attribute' => RequestKeys::DATA])]);
    }

    /** @test */
    public function it_requires_a_message_parameter()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MESSAGE]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::MESSAGE])]);
    }

    /** @test */
    public function its_message_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::MESSAGE => 123]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MESSAGE]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::MESSAGE])]);
    }

    /** @test */
    public function its_message_parameter_must_have_less_than_256_characters()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::MESSAGE => $this->faker->regexify('[a-zA-Z0-9]{256}')]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::MESSAGE]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', [
                'attribute' => RequestKeys::MESSAGE,
                'max'       => 255,
            ]),
        ]);
    }

    /** @test */
    public function it_requires_a_source_event_parameter()
    {
        $request   = $this->formRequest($this->requestClass);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SOURCE_EVENT);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SOURCE_EVENT]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_source_event_parameter_must_be_a_string()
    {
        $request   = $this->formRequest($this->requestClass, [RequestKeys::SOURCE_EVENT => 123]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SOURCE_EVENT);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SOURCE_EVENT]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_source_event_must_a_valid_value()
    {
        $request   = $this->formRequest($this->requestClass, [RequestKeys::SOURCE_EVENT => 'invalid']);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SOURCE_EVENT);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SOURCE_EVENT]);
        $request->assertValidationMessages([Lang::get('validation.in', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_requires_a_user_id_parameter()
    {
        $request   = $this->formRequest($this->requestClass);
        $attribute = $this->getDisplayableAttribute(RequestKeys::USER_ID);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::USER_ID]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_user_id_parameter_must_be_an_integer()
    {
        $request   = $this->formRequest($this->requestClass, [RequestKeys::USER_ID => 'invalid']);
        $attribute = $this->getDisplayableAttribute(RequestKeys::USER_ID);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::USER_ID]);
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_user_id_must_exists()
    {
        $this->refreshDatabaseForSingleTest();
        $request   = $this->formRequest($this->requestClass, [RequestKeys::USER_ID => 1000]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::USER_ID);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::USER_ID]);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_passes_on_valid_data()
    {
        $this->refreshDatabaseForSingleTest();
        $user     = User::factory()->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::DATA         => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            RequestKeys::MESSAGE      => 'message',
            RequestKeys::SOURCE_EVENT => InternalNotificationsSourceEvents::SUPPLIER_SOURCE_EVENTS[0],
            RequestKeys::USER_ID      => $user->getKey(),
        ]);

        $request->assertValidationPassed();
    }
}
