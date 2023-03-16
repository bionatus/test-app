<?php

namespace Tests\Feature\Api\V3\Account\Wishlist;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Account\WishlistController;
use App\Http\Resources\Api\V3\Account\Wishlist\BaseResource;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see WishlistController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ACCOUNT_WISHLIST_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_returns_a_user_list_of_wishlist_of_a_user_including_the_lists_created_without_user()
    {
        $user      = User::factory()->create();
        $wishlists = Wishlist::factory()->usingUser($user)->count(5)->create();
        Wishlist::factory()->count(2)->create();

        $this->login($user);
        $response = $this->get(URL::route($this->routeName, [RouteParameters::USER => $user->getRouteKey()]));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount($response->json('meta.total'), $wishlists);
    }

    /** @test */
    public function it_returns_a_user_list_of_wishlist_sorted_by_newest()
    {
        $user = User::factory()->create();
        $now  = Carbon::now();

        $first  = Wishlist::factory()->usingUser($user)->create(['created_at' => $now->subDay()]);
        $third  = Wishlist::factory()->usingUser($user)->create(['created_at' => $now->subDays(3)]);
        $second = Wishlist::factory()->usingUser($user)->create(['created_at' => $now->addDays(2)]);

        $expected = Collection::make([$first, $second, $third]);

        $this->login($user);
        $response = $this->get(URL::route($this->routeName, [RouteParameters::USER => $user->getRouteKey()]));

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawWishlist, int $index) use ($expected) {
            $this->assertEquals($expected->get($index)->getRouteKey(), $rawWishlist['id']);
        });
    }
}
