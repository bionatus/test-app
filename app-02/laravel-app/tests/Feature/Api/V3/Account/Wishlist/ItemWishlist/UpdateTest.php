<?php

namespace Tests\Feature\Api\V3\Account\Wishlist\ItemWishlist;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Account\Wishlist\ItemWishlistController;
use App\Http\Requests\Api\V3\Account\Wishlist\ItemWishlist\UpdateRequest;
use App\Http\Resources\Api\V3\Account\Wishlist\ItemWishlist\BaseResource;
use App\Models\ItemWishlist;
use App\Models\Part;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ItemWishlistController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_ACCOUNT_WISHLIST_ITEM_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $itemWishlist = ItemWishlist::factory()->create();
        $route        = URL::route($this->routeName, [$itemWishlist->wishlist, $itemWishlist]);

        $this->patch($route);
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:update,' . RouteParameters::ITEM_WISHLIST]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, UpdateRequest::class);
    }

    /** @test */
    public function it_can_update_its_item_wishlist_quantity()
    {
        $user         = User::factory()->create();
        $wishlist     = Wishlist::factory()->usingUser($user)->create();
        $part         = Part::factory()->create();
        $itemWishlist = ItemWishlist::factory()
            ->usingWishlist($wishlist)
            ->usingItem($part->item)
            ->create(['quantity' => 3]);

        $this->login($user);

        $route = URL::route($this->routeName, [
            RouteParameters::WISHLIST      => $itemWishlist->wishlist->getRouteKey(),
            RouteParameters::ITEM_WISHLIST => $itemWishlist->getRouteKey(),
        ]);

        $response = $this->patch($route, [
            RequestKeys::QUANTITY => $newQuantity = 5,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($data['id'], $itemWishlist->getRouteKey());
        $this->assertEquals($data['quantity'], $newQuantity);
    }

    /** @test */
    public function it_can_not_update_an_item_wishlist_of_other_user()
    {
        $user         = User::factory()->create();
        $wishlist     = Wishlist::factory()->usingUser($user)->create();
        $part         = Part::factory()->create();
        $itemWishlist = ItemWishlist::factory()
            ->usingWishlist($wishlist)
            ->usingItem($part->item)
            ->create(['quantity' => 3]);

        $otherUser = User::factory()->create();

        $this->login($otherUser);

        $route = URL::route($this->routeName, [
            RouteParameters::WISHLIST      => $itemWishlist->wishlist->getRouteKey(),
            RouteParameters::ITEM_WISHLIST => $itemWishlist->getRouteKey(),
        ]);

        $response = $this->patch($route, [
            RequestKeys::QUANTITY => 5,
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
