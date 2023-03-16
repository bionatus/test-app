<?php

namespace Tests\Feature\Api\V3\Account\Wishlist\ItemWishlist;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\Account\Wishlist\ItemWishlistController;
use App\Http\Requests\Api\V3\Account\Wishlist\ItemWishlist\StoreRequest;
use App\Http\Resources\Api\V3\Account\Wishlist\ItemWishlist\BaseResource;
use App\Models\ItemWishlist;
use App\Models\Part;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ItemWishlistController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_ACCOUNT_WISHLIST_ITEM_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);
        $wishlist = Wishlist::factory()->create();

        $this->post(URL::route($this->routeName, $wishlist));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_stores_an_item_wishlist_into_an_existing_wishlist()
    {
        $user     = User::factory()->create();
        $part     = Part::factory()->create();
        $item     = $part->item;
        $quantity = 3;
        $wishlist = Wishlist::factory()->usingUser($user)->create();

        $this->login($user);
        $route    = URL::route($this->routeName, $wishlist);
        $response = $this->post($route, [
            RequestKeys::ITEM     => $item->getRouteKey(),
            RequestKeys::QUANTITY => $quantity,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertDatabaseCount(ItemWishlist::tableName(), 1);
        $this->assertDatabaseHas(ItemWishlist::tableName(), [
            'wishlist_id' => $wishlist->getKey(),
            'item_id'     => $item->getKey(),
            'quantity'    => $quantity,
        ]);
    }

    /** @test */
    public function it_can_not_add_an_item_wishlist_of_other_user()
    {
        $user     = User::factory()->create();
        $part     = Part::factory()->create();
        $item     = $part->item;
        $quantity = 3;
        $wishlist = Wishlist::factory()->usingUser($user)->create();

        $otherUser = User::factory()->create();

        $this->login($otherUser);
        $route    = URL::route($this->routeName, $wishlist);
        $response = $this->post($route, [
            RequestKeys::ITEM     => $item->getRouteKey(),
            RequestKeys::QUANTITY => $quantity,
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
