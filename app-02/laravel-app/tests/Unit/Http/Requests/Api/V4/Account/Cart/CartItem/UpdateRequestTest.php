<?php

namespace Tests\Unit\Http\Requests\Api\V4\Account\Cart\CartItem;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V4\Account\Cart\CartItem\UpdateRequest;
use Lang;
use Tests\Unit\Http\Requests\FormRequestTest;

class UpdateRequestTest extends FormRequestTest
{
    protected string $requestClass = UpdateRequest::class;

    /** @test */
    public function its_quantity_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::QUANTITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::QUANTITY);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_quantity_parameter_should_be_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::QUANTITY => 'string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::QUANTITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::QUANTITY);
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_quantity_parameter_should_greater_than_1()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::QUANTITY => 0]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::QUANTITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::QUANTITY);
        $request->assertValidationMessages([
            Lang::get('validation.min.numeric', ['attribute' => $attribute, 'min' => 1]),
        ]);
    }

    /** @test */
    public function it_should_pass_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::QUANTITY => 15,
        ]);

        $request->assertValidationPassed();
    }
}
