<?php

namespace Tests\Feature\Api\V4\Order\Delivery\Pickup\Confirm;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V4\Order\Delivery\Pickup\ConfirmController;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ConfirmController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V4_ORDER_DELIVERY_PICKUP_CONFIRM_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:confirmPickup,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_confirms_user_pickup_the_order()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($user)->approved()->create();

        OrderDelivery::factory()->usingOrder($order)->pickup()->create();

        $this->login($user);
        $route    = URL::route($this->routeName, $order);
        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_APPROVED_DELIVERED,
        ]);
    }
}
