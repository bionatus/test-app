<?php

namespace Tests\Unit\Http\Requests\Api\V3\Account\Wishlist\ItemWishlist;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Account\Wishlist\ItemWishlistController;
use App\Http\Requests\Api\V3\Account\Wishlist\ItemWishlist\UpdateRequest;
use App\Models\ItemWishlist;
use App\Models\Part;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;
use URL;

/** @see ItemWishlistController */
class UpdateRequestTest extends RequestTestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected string $requestClass = UpdateRequest::class;
    private string   $route;

    protected function setUp(): void
    {
        parent::setUp();

        $part        = Part::factory()->functional()->create();
        $routeParams = [
            RouteParameters::WISHLIST      => $wishlist = Wishlist::factory()->create(),
            RouteParameters::ITEM_WISHLIST => ItemWishlist::factory()
                ->usingWishlist($wishlist)
                ->usingItem($part->item)
                ->create(),
        ];
        $this->route = URL::route(RouteNames::API_V3_ACCOUNT_WISHLIST_ITEM_UPDATE, $routeParams);
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
