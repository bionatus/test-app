<?php

namespace Tests\Feature\Api\V3\Account\Wishlist\ItemWishlist;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Resources\Api\V3\Account\Wishlist\ItemWishlist\BaseResource;
use App\Models\ItemWishList;
use App\Models\Supply;
use App\Models\User;
use App\Models\WishList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see ItemWishListController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ACCOUNT_WISHLIST_ITEM_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName, WishList::factory()->create()));
    }

    /** @test */
    public function it_returns_a_list_of_wishlist_items()
    {
        $user     = User::factory()->create();
        $wishlist = WishList::factory()->usingUser($user)->create();
        WishList::factory()->create();

        $item        = Supply::factory()->create()->item;
        $anotherItem = Supply::factory()->create()->item;

        $itemWishlists = Collection::make();
        $itemWishlists->push(ItemWishList::factory()->usingWishlist($wishlist)->usingItem($item)->create());
        $itemWishlists->push(ItemWishList::factory()->usingWishlist($wishlist)->usingItem($anotherItem)->create());

        $route = URL::route($this->routeName, [RouteParameters::WISHLIST => $wishlist->getRouteKey()]);
        $this->login($user);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $data = Collection::make($response->json('data'));
        $this->assertCount(2, $data);

        $data->each(function(array $rawWishlistItem, int $index) use ($itemWishlists) {
            $itemWishlist = $itemWishlists->get($index);
            $this->assertSame($itemWishlist->getRouteKey(), $rawWishlistItem['id']);
        });
    }

    /** @test */
    public function it_can_not_list_items_of_another_user_wishlist()
    {
        $user     = User::factory()->create();
        $wishlist = WishList::factory()->usingUser($user)->create();

        $otherUser = User::factory()->create();
        WishList::factory()->usingUser($otherUser)->create();

        $item        = Supply::factory()->create()->item;
        $anotherItem = Supply::factory()->create()->item;

        $itemWishlists = Collection::make();
        $itemWishlists->push(ItemWishList::factory()->usingWishlist($wishlist)->usingItem($item)->create());
        $itemWishlists->push(ItemWishList::factory()->usingWishlist($wishlist)->usingItem($anotherItem)->create());

        $route = URL::route($this->routeName, [RouteParameters::WISHLIST => $wishlist->getRouteKey()]);

        $this->login($otherUser);
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}

