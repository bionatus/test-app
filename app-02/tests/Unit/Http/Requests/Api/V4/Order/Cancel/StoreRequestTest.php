<?php

namespace Tests\Unit\Http\Requests\Api\V4\Order\Cancel;

use App\Constants\RequestKeys;
use App\Http\Controllers\Api\V4\Order\CancelController;
use App\Http\Requests\Api\V4\Order\Cancel\StoreRequest;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see CancelController */
class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    private string   $route;
    private Order    $order;
    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function its_status_detail_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATUS_DETAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATUS_DETAIL);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_status_detail_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATUS_DETAIL => 1]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATUS_DETAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATUS_DETAIL);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $attribute]),
        ]);
    }

    public function its_status_detail_parameter_must_be_at_least_5_characters_long()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATUS_DETAIL => Str::random(4)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATUS_DETAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATUS_DETAIL);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => $attribute, 'min' => 5]),
        ]);
    }

    /** @test */
    public function its_status_detail_parameter_must_be_at_most_255_characters_long()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATUS_DETAIL => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATUS_DETAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATUS_DETAIL);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_pass_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATUS_DETAIL => Str::random(10)]);

        $request->assertValidationPassed();
    }
}
