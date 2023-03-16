<?php

namespace Tests\Unit\Http\Requests\LiveApi\V2\Order\ItemOrder\CustomItem;

use App\Constants\RequestKeys;
use App\Http\Controllers\LiveApi\V2\Order\ItemOrder\CustomItemController;
use App\Http\Requests\LiveApi\V2\Order\ItemOrder\CustomItem\StoreRequest;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see CustomItemController */
class StoreRequestTest extends RequestTestCase
{
    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function it_requires_a_name_parameter()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NAME);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_name_parameter_must_be_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => 122]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NAME);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_name_parameter_should_have_at_least_3_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => 'aw']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NAME);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => $attribute, 'min' => 3]),
        ]);
    }

    /** @test */
    public function it_should_limit_the_name_to_40_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => Str::random(41)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NAME);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 40]),
        ]);
    }

    /** @test */
    public function it_requires_a_quantity_parameter()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::QUANTITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::QUANTITY);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_quantity_parameter_must_be_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::QUANTITY => 'adfa122']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::QUANTITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::QUANTITY);
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_quantity_parameter_should_be_at_least_1()
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
    public function it_should_limit_the_quantity_to_999_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::QUANTITY => 1000]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::QUANTITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::QUANTITY);
        $request->assertValidationMessages([
            Lang::get('validation.max.numeric', ['attribute' => $attribute, 'max' => 999]),
        ]);
    }

    /** @test */
    public function it_passes_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::NAME     => 'custom item name',
            RequestKeys::QUANTITY => 1,
        ]);

        $request->assertValidationPassed();
    }
}
