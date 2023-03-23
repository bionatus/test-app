<?php

namespace Tests\Feature\LiveApi\V2\Order\Complete;

use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Events\Order\Completed;
use App\Http\Controllers\LiveApi\V2\Order\CompleteController;
use App\Http\Resources\LiveApi\V2\Order\DetailedResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderStaff;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use Auth;
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

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_COMPLETE_STORE;

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
        $this->assertRouteUsesMiddleware($this->routeName, ['can:complete,' . RouteParameters::ORDER]);
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_sets_order_status_to_completed(string $orderDeliveryType, int $substatusId)
    {
        Event::fake(Completed::class);

        $staff    = Staff::factory()->createQuietly(['name' => 'Example']);
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->approved()->create();
        OrderStaff::factory()->usingStaff($staff)->usingOrder($order)->create();
        OrderDelivery::factory()->usingOrder($order)->create([
            'type' => $orderDeliveryType,
        ]);
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        Auth::shouldUse('live');
        $this->login($staff);
        $parameters = [
            RouteParameters::ORDER => $order->getRouteKey(),
        ];
        if ($orderDeliveryType === OrderDelivery::TYPE_PICKUP) {
            $parameters[RequestKeys::TOTAL] = $total = 100;
        }
        $response = $this->post(URL::route($this->routeName, $parameters));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema()), $response);
        if ($orderDeliveryType === OrderDelivery::TYPE_PICKUP) {
            $this->assertDatabaseHas(Order::tableName(), ['id' => $order->getKey(), 'total' => $total * 100]);
        }
        $this->assertDatabaseHas(OrderSubstatus::tableName(),
            ['order_id' => $order->getKey(), 'substatus_id' => Substatus::STATUS_COMPLETED_DONE]);
    }

    public function dataProvider(): array
    {
        return [
            [OrderDelivery::TYPE_PICKUP, Substatus::STATUS_APPROVED_READY_FOR_DELIVERY],
            [OrderDelivery::TYPE_PICKUP, Substatus::STATUS_APPROVED_DELIVERED],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, Substatus::STATUS_APPROVED_AWAITING_DELIVERY],
        ];
    }

    /** @test */
    public function it_dispatches_a_completed_event()
    {
        Event::fake([Completed::class]);

        $staff    = Staff::factory()->createQuietly(['name' => 'Example']);
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->approved()->create();
        OrderStaff::factory()->usingStaff($staff)->usingOrder($order)->create();
        OrderDelivery::factory()->usingOrder($order)->create([
            'type' => OrderDelivery::TYPE_SHIPMENT_DELIVERY,
        ]);
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_APPROVED_AWAITING_DELIVERY)
            ->create();
        Auth::shouldUse('live');
        $this->login($staff);

        $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]));

        Event::assertDispatched(Completed::class);
    }
}
