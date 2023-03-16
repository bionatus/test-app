<?php

namespace Tests\Feature\Api\V3\Order\Share;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Models\Order;
use App\Models\SharedOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ShareController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_ORDER_SHARE_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName,
            [RouteParameters::ORDER => Order::factory()->createQuietly()->getRouteKey()]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:share,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_shares_an_order()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();

        $this->login($user);
        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseHas(SharedOrder::tableName(), [
            'order_id' => $order->getKey(),
            'user_id'  => $order->user_id,
        ]);
    }
}
