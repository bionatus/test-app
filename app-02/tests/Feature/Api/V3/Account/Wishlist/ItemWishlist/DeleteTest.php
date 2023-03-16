<?php

namespace Tests\Feature\Api\V3\Account\Wishlist\ItemWishlist;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Models\ItemWishlist;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ItemWishlistController */
class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_ACCOUNT_WISHLIST_ITEM_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);
        $itemWishlist = ItemWishlist::factory()->create();

        $this->delete(URL::route($this->routeName, [$itemWishlist->wishlist, $itemWishlist]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:delete,' . RouteParameters::ITEM_WISHLIST]);
    }

    /** @test */
    public function it_deletes_an_item_of_the_user_wishlist()
    {
        $user     = User::factory()->create();
        $wishlist = Wishlist::factory()->usingUser($user)->create();

        $itemWishlist = ItemWishlist::factory()->usingWishlist($wishlist)->create();

        $routeParams = [
            RouteParameters::WISHLIST      => $itemWishlist->wishlist,
            RouteParameters::ITEM_WISHLIST => $itemWishlist,
        ];
        $route       = URL::route($this->routeName, $routeParams);
        $this->login($user);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(ItemWishlist::tableName(), [
            'wishlist_id' => $wishlist->getKey(),
            'item_id'     => $itemWishlist->item_id,
        ]);
    }

    /** @test */
    public function it_can_not_delete_an_item_wishlist_of_other_user()
    {
        $user     = User::factory()->create();
        $wishlist = Wishlist::factory()->usingUser($user)->create();

        $otherUser = User::factory()->create();

        $itemWishlist = ItemWishlist::factory()->usingWishlist($wishlist)->create();

        $routeParams = [
            RouteParameters::WISHLIST      => $itemWishlist->wishlist,
            RouteParameters::ITEM_WISHLIST => $itemWishlist,
        ];
        $route       = URL::route($this->routeName, $routeParams);
        $this->login($otherUser);
        $response = $this->delete($route);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
