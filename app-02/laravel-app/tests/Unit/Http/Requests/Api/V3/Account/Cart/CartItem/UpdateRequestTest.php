<?php

namespace Tests\Unit\Http\Requests\Api\V3\Account\Cart\CartItem;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Account\Cart\CartItemController;
use App\Http\Requests\Api\V3\Account\Cart\CartItem\UpdateRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Part;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;
use URL;

/** @see CartItemController */
class UpdateRequestTest extends RequestTestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected string $requestClass = UpdateRequest::class;
    private CartItem $cartItem;
    private string   $route;

    protected function setUp(): void
    {
        parent::setUp();

        $part           = Part::factory()->functional()->create();
        $cart           = Cart::factory()->create();
        $this->cartItem = CartItem::factory()->usingCart($cart)->usingItem($part->item)->create();

        $this->route = URL::route(RouteNames::API_V3_ACCOUNT_CART_ITEM_UPDATE,
            [RouteParameters::CART_ITEM => $this->cartItem]);
    }

    /** @test */
    public function its_quantity_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::QUANTITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::QUANTITY);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_quantity_parameter_must_be_an_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::QUANTITY => 'da1234'],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::QUANTITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::QUANTITY);
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_quantity_parameter_must_be_greater_than_0()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::QUANTITY => 0],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::QUANTITY]);
        $request->assertValidationMessages([
            Lang::get('validation.min.numeric', [
                'attribute' => RequestKeys::QUANTITY,
                'min'       => 1,
            ]),
        ]);
    }

    /** @test */
    public function it_pass_on_valid_data()
    {
        $data    = [RequestKeys::QUANTITY => 1,];
        $request = $this->formRequest($this->requestClass, $data, ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationPassed();
    }
}
