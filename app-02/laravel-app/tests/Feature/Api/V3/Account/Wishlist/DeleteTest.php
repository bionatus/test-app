<?php

namespace Tests\Feature\Api\V3\Account\Wishlist;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see WishlistController */
class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_ACCOUNT_WISHLIST_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $wishlist = Wishlist::factory()->create();

        $this->delete(URL::route($this->routeName, [RouteParameters::WISHLIST => $wishlist->getRouteKey()]));
    }

    /** @test */
    public function it_deletes_an_user_wishlist()
    {
        $user     = User::factory()->create();
        $wishlist = Wishlist::factory()->usingUser($user)->create();

        $this->login($user);
        $url = URL::route($this->routeName, [RouteParameters::WISHLIST => $wishlist->getRouteKey()]);

        $response = $this->delete($url);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertModelMissing($wishlist);
    }
}
