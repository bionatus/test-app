<?php

namespace Tests\Feature\Api\V4\Order\Complete;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\Completed;
use App\Http\Controllers\Api\V4\Order\CompleteController;
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

/** @see CompleteController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V4_ORDER_COMPLETE_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:complete,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_sets_order_status_to_completed()
    {
        Event::fake(Completed::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->approved()->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId(Substatus::STATUS_APPROVED_DELIVERED)->create();
        OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create();

        $this->login($user);

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(OrderSubstatus::tableName(),
            ['order_id' => $order->getKey(), 'substatus_id' => Substatus::STATUS_COMPLETED_DONE]);
    }

    /** @test */
    public function it_dispatches_a_completed_event()
    {
        Event::fake(Completed::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($user)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId(Substatus::STATUS_APPROVED_DELIVERED)->create();
        OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create();

        $this->login($user);

        $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        Event::assertDispatched(Completed::class);
    }
}
