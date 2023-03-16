<?php

namespace Tests\Unit\Http\Requests\LiveApi\V1\Order\Assignment;

use App\Constants\RequestKeys;
use App\Http\Controllers\LiveApi\V1\Order\AssignController;
use App\Http\Requests\LiveApi\V1\Order\Assignment\StoreRequest;
use Illuminate\Foundation\Testing\WithFaker;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see AssignController */
class StoreRequestTest extends RequestTestCase
{
    use WithFaker;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function its_name_parameter_must_be_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NAME);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_name_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => 1234]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::NAME);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_name_parameter_must_be_a_string_of_no_more_than_255_characters()
    {
        $request = $this->formRequest($this->requestClass,
            [RequestKeys::NAME => $this->faker->regexify('[a-zA-Z0-9]{256}')]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', [
                'attribute' => RequestKeys::NAME,
                'max'       => 255,
            ]),
        ]);
    }

    /** @test */
    public function it_pass_on_valid_data()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => 'John Doe']);

        $request->assertValidationPassed();
    }
}
