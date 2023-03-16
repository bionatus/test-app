<?php

namespace Tests\Unit\Http\Requests\Api\V3\InternalNotification;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V3\InternalNotificationController;
use App\Http\Requests\Api\V3\InternalNotification\IndexRequest;
use Lang;
use Tests\CanRefreshDatabase;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see InternalNotificationController */
class IndexRequestTest extends RequestTestCase
{
    use CanRefreshDatabase;

    protected string $requestClass = IndexRequest::class;

    /** @test */
    public function its_read_parameter_must_be_a_boolean()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::READ => 'invalid']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::READ]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::READ);
        $request->assertValidationMessages([Lang::get('validation.boolean', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::READ => 1,
        ]);

        $request->assertValidationPassed();
    }
}
