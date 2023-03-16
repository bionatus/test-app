<?php

namespace Tests\Feature\Api\V4\Order\Delivery\Shipment\Approve;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\Approved;
use App\Http\Controllers\Api\V4\Order\Delivery\Shipment\ApproveController;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ApproveController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V4_ORDER_DELIVERY_SHIPMENT_APPROVE_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:approveShipment,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_approves_shipment_delivery()
    {
        Event::fake(Approved::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($user)->approved()->create();

        OrderDelivery::factory()->usingOrder($order)->shipmentDelivery()->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED)
            ->create();

        $this->login($user);
        $route    = URL::route($this->routeName, $order);
        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
        ]);
    }

    /** @test */
    public function it_dispatches_approve_event()
    {
        $this->withoutExceptionHandling();

        Event::fake(Approved::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($user)->approved()->create();

        OrderDelivery::factory()->usingOrder($order)->shipmentDelivery()->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED)
            ->create();

        $this->login($user);
        $route    = URL::route($this->routeName, $order);
        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);
        Event::assertDispatched(Approved::class);
    }
}
