<?php

namespace Tests\Unit\Http\Requests\Api\V3\Account\Cart\CartItem;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Account\Cart\CartItem\StoreRequest;
use App\Models\CartItem;
use App\Models\Item;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;

class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = StoreRequest::class;

    /** @test */
    public function its_item_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEM]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ITEM);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_item_parameter_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEM => 2]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEM]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ITEM);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_item_parameter_must_exist()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEM => 'not exists']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEM]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ITEM);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_item_parameter_must_be_unique_in_the_cart()
    {
        $cartItem = CartItem::factory()->create();
        $item     = $cartItem->item;
        Auth::login($cartItem->cart->user);

        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEM => $item->getRouteKey()]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEM]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ITEM);
        $request->assertValidationMessages([
            Lang::get('This :attribute already exists on the cart', ['attribute' => $attribute]),
        ]);
    }

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
    public function its_quantity_parameter_should_be_an_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::QUANTITY => 'string']);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::QUANTITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::QUANTITY);
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_quantity_parameter_should_be_greater_than_zero()
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
        $item = Item::factory()->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::ITEM     => $item->getRouteKey(),
            RequestKeys::QUANTITY => 15,
        ]);

        $request->assertValidationPassed();
    }
}
