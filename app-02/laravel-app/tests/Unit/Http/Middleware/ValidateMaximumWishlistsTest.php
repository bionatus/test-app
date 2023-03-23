<?php

namespace Tests\Unit\Http\Middleware;

use App;
use App\Http\Middleware\ValidateMaximumWishlists;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ValidateMaximumWishlistsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_reject_if_user_has_already_ten_wishlists()
    {
        $user = User::factory()->create();
        Wishlist::factory()->usingUser($user)->count(10)->create();

        $this->login($user);

        $requestMock = Mockery::mock(Request::class);

        $middleware = App::make(ValidateMaximumWishlists::class);

        $response = $middleware->handle($requestMock, fn() => null);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_should_accept_if_user_has_less_than_ten_wishlists()
    {
        $user = User::factory()->create();
        Wishlist::factory()->usingUser($user)->count(9)->create();

        $this->login($user);

        $requestMock = Mockery::mock(Request::class);

        $middleware = App::make(ValidateMaximumWishlists::class);

        $response = $middleware->handle($requestMock, fn() => null);

        $this->assertNull($response);
    }
}
