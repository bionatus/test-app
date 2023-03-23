<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Order\CustomItem;

use App\Constants\RequestKeys;
use App\Http\Controllers\LiveApi\V1\Order\CustomItemController;
use App\Http\Requests\LiveApi\V1\Order\CustomItem\StoreRequest;
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
    public function it_should_minimo_the_name_to_2_chars()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => 'a']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NAME);
        $request->assertValidationMessages([
            Lang::get('validation.min.string', ['attribute' => $attribute, 'min' => 2]),
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
    public function it_passes_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::NAME => 'custom item name',
        ]);

        $request->assertValidationPassed();
    }
}
