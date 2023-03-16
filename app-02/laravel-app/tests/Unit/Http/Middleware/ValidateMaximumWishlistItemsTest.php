<?php

namespace Tests\Unit\Http\Middleware;

use App;
use App\Constants\RouteParameters;
use App\Http\Middleware\ValidateMaximumWishlistItems;
use App\Models\ItemWishlist;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ValidateMaximumWishlistItemsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_reject_if_user_has_already_fifty_different_items_in_the_wishlist()
    {
        $wishlist = Wishlist::factory()->create();
        ItemWishlist::factory()->usingWishlist($wishlist)->count(50)->create();

        $routeMock = Mockery::mock(Route::class);
        $routeMock->shouldReceive('parameter')->withArgs([RouteParameters::WISHLIST])->once()->andReturn($wishlist);
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('route')->withNoArgs()->once()->andReturn($routeMock);

        $middleware = App::make(ValidateMaximumWishlistItems::class);

        $response = $middleware->handle($requestMock, function() {
        });
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_should_accept_if_user_has_less_than_fifty_items_in_the_wishlists()
    {
        $wishlist = Wishlist::factory()->create();
        ItemWishlist::factory()->usingWishlist($wishlist)->count(49)->create();

        $routeMock = Mockery::mock(Route::class);
        $routeMock->shouldReceive('parameter')->withArgs([RouteParameters::WISHLIST])->once()->andReturn($wishlist);
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('route')->withNoArgs()->once()->andReturn($routeMock);

        $middleware = App::make(ValidateMaximumWishlistItems::class);

        $response = $middleware->handle($requestMock, fn() => null);

        $this->assertNull($response);
    }
}
