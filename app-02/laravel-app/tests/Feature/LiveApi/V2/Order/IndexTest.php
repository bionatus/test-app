<?php

namespace Tests\Feature\LiveApi\V2\Order;

use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Http\Controllers\LiveApi\V2\OrderController;
use App\Http\Requests\LiveApi\V2\Order\IndexRequest;
use App\Http\Resources\LiveApi\V2\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderStaff;
use App\Models\Staff;
use App\Models\Substatus;
use App\Models\Supplier;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see OrderController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, IndexRequest::class);
    }

    /** @test */
    public function it_displays_a_order_list_filtered_by_price_and_availability_requests_statuses_and_sorted_by_updated_at(
    )
    {
        $expectedOrders = Collection::make([]);

        $now      = Carbon::now();
        $supplier = Supplier::factory()->createQuietly();

        Carbon::setTestNow($now->clone()->subDay());
        $orderPR = Order::factory()
            ->usingSupplier($supplier)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_PENDING_REQUESTED))
            ->create();

        Carbon::setTestNow($now->clone()->subDays(4));
        $orderPA = Order::factory()
            ->usingSupplier($supplier)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_PENDING_ASSIGNED))
            ->create();

        $expectedOrders->add($orderPR);
        $expectedOrders->add($orderPA);

        $anySubstatus = Substatus::factory()->create();
        $order        = Order::factory()->usingSupplier($supplier)->usingSubstatus($anySubstatus)->create();
        $staff        = Staff::factory()->createQuietly(['name' => 'fake Name']);
        OrderStaff::factory()->usingStaff($staff)->usingOrder($order)->create();

        $route = URL::route($this->routeName, [RequestKeys::TYPE => 'availability-requests']);

        Carbon::setTestNow($now);
        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));

        $this->assertCount($response->json('meta.total'), $expectedOrders);
        $data->each(function(array $rawOrder, int $index) use ($expectedOrders) {
            $order = $expectedOrders->get($index);
            $this->assertSame($order->getRouteKey(), $rawOrder['id']);
        });
    }

    /** @test */
    public function it_displays_a_order_list_filtered_by_will_call_and_approved_orders_and_sorted_by_updated_at()
    {
        $expectedOrders = Collection::make([]);

        $now       = Carbon::now();
        $supplier  = Supplier::factory()->createQuietly();
        $orderPAQN = Order::factory()
            ->usingSupplier($supplier)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED))
            ->create(['updated_at' => $now->subDay()]);

        $orderAAD = Order::factory()
            ->usingSupplier($supplier)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_APPROVED_AWAITING_DELIVERY))
            ->create(['updated_at' => $now->subDays(4)]);

        $orderARD = Order::factory()
            ->usingSupplier($supplier)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_APPROVED_READY_FOR_DELIVERY))
            ->create(['updated_at' => $now->subDays(2)]);

        $orderPickupAD = Order::factory()
            ->usingSupplier($supplier)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_APPROVED_DELIVERED))
            ->create(['updated_at' => $now]);
        OrderDelivery::factory()->usingOrder($orderPickupAD)->pickup()->create();

        $otherDelivery = Order::factory()
            ->usingSupplier($supplier)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_APPROVED_DELIVERED))
            ->create(['updated_at' => $now]);
        OrderDelivery::factory()->usingOrder($otherDelivery)->shipmentDelivery()->create();

        $expectedOrders->add($orderPickupAD);
        $expectedOrders->add($orderARD);
        $expectedOrders->add($orderPAQN);
        $expectedOrders->add($orderAAD);

        $anySubstatus = Substatus::factory()->create();
        Order::factory()->usingSubstatus($anySubstatus)->create();

        $route = URL::route($this->routeName, [RequestKeys::TYPE => 'availability-requests']);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawPart, int $index) use ($expectedOrders) {
            $part = $expectedOrders->get($index);
            $this->assertSame($part->item->getRouteKey(), $rawPart['id']);
        });
    }


    /** @test */
    public function it_displays_a_order_list_without_filters_and_sorted_by_updated_at()
    {
        $expectedOrders = Collection::make([]);

        $now       = Carbon::now();
        $supplier  = Supplier::factory()->createQuietly();
        $orderPAQN = Order::factory()
            ->usingSupplier($supplier)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED))
            ->create(['updated_at' => $now->subDay()]);

        $orderAAD = Order::factory()
            ->usingSupplier($supplier)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_APPROVED_AWAITING_DELIVERY))
            ->create(['updated_at' => $now->subDays(4)]);

        $orderARD = Order::factory()
            ->usingSupplier($supplier)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_APPROVED_READY_FOR_DELIVERY))
            ->create(['updated_at' => $now->subDays(2)]);

        $orderPickupAD = Order::factory()
            ->usingSupplier($supplier)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_APPROVED_DELIVERED))
            ->create(['updated_at' => $now]);
        OrderDelivery::factory()->usingOrder($orderPickupAD)->pickup()->create();

        $otherDelivery = Order::factory()
            ->usingSupplier($supplier)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_APPROVED_DELIVERED))
            ->create(['updated_at' => $now]);
        OrderDelivery::factory()->usingOrder($otherDelivery)->shipmentDelivery()->create();

        $expectedOrders->add($orderPickupAD);
        $expectedOrders->add($orderARD);
        $expectedOrders->add($orderPAQN);
        $expectedOrders->add($orderAAD);
        $expectedOrders->add($otherDelivery);

        $anySubstatus = Substatus::factory()->create();
        Order::factory()->usingSubstatus($anySubstatus)->create();

        $route = URL::route($this->routeName, []);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->createQuietly());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'));

        $data->each(function(array $rawPart, int $index) use ($expectedOrders) {
            $part = $expectedOrders->get($index);
            $this->assertSame($part->item->getRouteKey(), $rawPart['id']);
        });
    }
}
