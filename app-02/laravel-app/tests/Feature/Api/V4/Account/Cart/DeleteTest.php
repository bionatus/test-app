<?php

namespace Tests\Feature\Api\V4\Account\Cart;

use App\Constants\RouteNames;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see CartController */
class DeleteTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V4_ACCOUNT_CART_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->delete(URL::route($this->routeName));
    }

    /** @test */
    public function it_deletes_the_user_cart()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->usingUser($user)->create();

        $this->login($user);

        $response = $this->delete(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertModelMissing($cart);
    }

    /** @test */
    public function it_tries_to_delete_the_user_cart_if_not_exist()
    {
        $user = User::factory()->create();

        $this->assertDatabaseMissing(Cart::tableName(), [
            'user_id' => $user->getKey(),
        ]);

        $this->login($user);

        $response = $this->delete(URL::route($this->routeName));
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }
}
