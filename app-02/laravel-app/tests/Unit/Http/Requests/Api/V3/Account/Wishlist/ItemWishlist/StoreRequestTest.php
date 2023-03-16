<?php

namespace Tests\Unit\Http\Requests\Api\V3\Account\Wishlist\ItemWishlist;

use App\Http\Controllers\Api\V3\Account\Wishlist\ItemWishlistController;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Requests\Api\V3\Account\Wishlist\ItemWishlist\StoreRequest;
use App\Models\Item;
use App\Models\ItemWishlist;
use App\Models\Wishlist;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;
use URL;

/** @see ItemWishlistController */
class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = StoreRequest::class;
    private string   $route;
    private Wishlist $wishlist;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wishlist = Wishlist::factory()->createQuietly();
        $this->route    = URL::route(RouteNames::API_V3_ACCOUNT_WISHLIST_ITEM_STORE,
            [RouteParameters::WISHLIST => $this->wishlist]);
    }

    /** @test */
    public function it_should_authorize()
    {
        $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route])
            ->addRouteParameter(RouteParameters::WISHLIST, $this->wishlist->getRouteKey())
            ->assertAuthorized();
    }

    /** @test */
    public function its_item_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEM]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ITEM);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_item_parameter_should_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEM => 2],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEM]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ITEM);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_item_parameter_must_exist()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEM => 'not exists'],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEM]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ITEM);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_item_parameter_must_be_unique_in_the_wishlist()
    {
        $itemWishlist = ItemWishlist::factory()->usingWishlist($this->wishlist)->create();
        $item         = $itemWishlist->item;
        Auth::login($this->wishlist->user);

        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEM => $item->getRouteKey()],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEM]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::ITEM);
        $request->assertValidationMessages([
            Lang::get('This :attribute already exists on the wishlist', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_quantity_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::QUANTITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::QUANTITY);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_quantity_parameter_should_be_an_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::QUANTITY => 'string'],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::QUANTITY]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::QUANTITY);
        $request->assertValidationMessages([Lang::get('validation.integer', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_quantity_parameter_should_be_greater_than_zero()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::QUANTITY => 0],
            ['method' => 'post', 'route' => $this->route]);

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
        ], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationPassed();
    }
}
