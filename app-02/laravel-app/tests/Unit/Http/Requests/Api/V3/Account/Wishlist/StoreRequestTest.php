<?php

namespace Tests\Unit\Http\Requests\Api\V3\Account\Wishlist;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Account\Wishlist\StoreRequest;
use Lang;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;

/** @see WishlistController */
class StoreRequestTest extends RequestTestCase
{
    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function its_name_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => RequestKeys::NAME])]);
    }

    /** @test */
    public function its_name_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => ['array item']]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => RequestKeys::NAME])]);
    }

    /** @test */
    public function its_name_parameter_must_have_max_255_characters()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::NAME => Str::random(256)]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::NAME]);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', [
                'attribute' => $this->getDisplayableAttribute(RequestKeys::NAME),
                'max'       => 255,
            ]),
        ]);
    }

    /** @test */
    public function it_pass_on_valid_values()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::NAME => 'Fake wishlist name',
        ]);

        $request->assertValidationPassed();
    }
}
