<?php

namespace Tests\Feature\Api\V4\Order;

use App\Constants\RouteNames;
use App\Http\Controllers\Api\V4\OrderController;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see OrderController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V4_ORDER_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_returns_a_list_of_orders_filtered_by_user()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        Order::factory()->count(3)->usingSupplier($supplier)->create();
        $orders = Order::factory()->usingUser($user)->count(2)->usingSupplier($supplier)->create();
        $orders->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
            OrderSubstatus::factory()->usingOrder($order)->create();
        });

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount(2, $data);
    }

    /** @test */
    public function it_returns_a_list_of_orders_ordered_by_status()
    {
        $supplier             = Supplier::factory()->createQuietly();
        $user                 = User::factory()->create();
        $orderCompleted       = Order::factory()->usingUser($user)->usingSupplier($supplier)->completed()->create();
        $orderCanceled        = Order::factory()->usingUser($user)->usingSupplier($supplier)->canceled()->create();
        $orderPendingApproval = Order::factory()
            ->usingUser($user)
            ->usingSupplier($supplier)
            ->pendingApproval()
            ->create();
        $orderPending         = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create();
        $orderApproved        = Order::factory()->usingUser($user)->usingSupplier($supplier)->approved()->create();

        $orders          = Collection::make([
            $orderPendingApproval,
            $orderApproved,
            $orderPending,
            $orderCanceled,
            $orderCompleted,
        ]);
        $ordersRouteKeys = $orders->pluck(Order::routeKeyName())->toArray();

        $orders->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $this->login($user);
        $route         = URL::route($this->routeName);
        $response      = $this->get($route);
        $data          = Collection::make($response->json('data'));
        $dataRouteKeys = $data->pluck(Order::keyName())->toArray();

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals($ordersRouteKeys, $dataRouteKeys);
    }

    /** @test */
    public function it_returns_a_list_of_orders_ordered_by_created_at_when_the_orders_have_the_same_status()
    {
        $supplier  = Supplier::factory()->createQuietly();
        $user      = User::factory()->create();
        $createdAt = Carbon::now();
        $order1    = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create([
            'created_at' => $createdAt->clone()->addMinutes(20),
        ]);
        $order2    = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create([
            'created_at' => $createdAt->clone()->addMinutes(30),
        ]);
        $order3    = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create([
            'created_at' => $createdAt->clone()->addMinutes(10),
        ]);

        $orders          = Collection::make([
            $order2,
            $order1,
            $order3,
        ]);
        $ordersRouteKeys = $orders->pluck(Order::routeKeyName())->toArray();

        $orders->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
            OrderSubstatus::factory()->usingOrder($order)->create();
        });

        $this->login($user);
        $route         = URL::route($this->routeName);
        $response      = $this->get($route);
        $data          = Collection::make($response->json('data'));
        $dataRouteKeys = $data->pluck(Order::keyName())->toArray();

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals($ordersRouteKeys, $dataRouteKeys);
    }

    /** @test */
    public function it_returns_a_list_of_orders_ordered_by_created_at_when_the_orders_have_pending_or_approved_status()
    {
        $supplier       = Supplier::factory()->createQuietly();
        $user           = User::factory()->create();
        $createdAt      = Carbon::now();
        $orderPending1  = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create([
            'created_at' => $createdAt->clone()->addMinutes(30),
        ]);
        $orderPending2  = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create([
            'created_at' => $createdAt->clone()->addMinutes(20),
        ]);
        $orderApproved1 = Order::factory()->usingUser($user)->usingSupplier($supplier)->approved()->create([
            'created_at' => $createdAt->clone()->addMinutes(10),
        ]);
        $orderApproved2 = Order::factory()->usingUser($user)->usingSupplier($supplier)->approved()->create([
            'created_at' => $createdAt->clone()->addMinutes(40),
        ]);

        $orders          = Collection::make([
            $orderApproved2,
            $orderPending1,
            $orderPending2,
            $orderApproved1,
        ]);
        $ordersRouteKeys = $orders->pluck(Order::routeKeyName())->toArray();

        $orders->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
            OrderSubstatus::factory()->usingOrder($order)->create();
        });

        $this->login($user);
        $route         = URL::route($this->routeName);
        $response      = $this->get($route);
        $data          = Collection::make($response->json('data'));
        $dataRouteKeys = $data->pluck(Order::keyName())->toArray();

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals($ordersRouteKeys, $dataRouteKeys);
    }

    /** @test */
    public function it_returns_a_list_of_orders_ordered_by_created_at_when_the_orders_have_completed_or_canceled_status(
    )
    {
        $supplier        = Supplier::factory()->createQuietly();
        $user            = User::factory()->create();
        $createdAt       = Carbon::now();
        $orderCompleted1 = Order::factory()->usingUser($user)->usingSupplier($supplier)->completed()->create([
            'created_at' => $createdAt->clone()->addMinutes(10),
        ]);
        $orderCompleted2 = Order::factory()->usingUser($user)->usingSupplier($supplier)->completed()->create([
            'created_at' => $createdAt->clone()->addMinutes(30),
        ]);
        $orderCanceled1  = Order::factory()->usingUser($user)->usingSupplier($supplier)->canceled()->create([
            'created_at' => $createdAt->clone()->addMinutes(40),
        ]);
        $orderCanceled2  = Order::factory()->usingUser($user)->usingSupplier($supplier)->canceled()->create([
            'created_at' => $createdAt->clone()->addMinutes(20),
        ]);

        $orders          = Collection::make([
            $orderCanceled1,
            $orderCompleted2,
            $orderCanceled2,
            $orderCompleted1,
        ]);
        $ordersRouteKeys = $orders->pluck(Order::routeKeyName())->toArray();

        $orders->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
            OrderSubstatus::factory()->usingOrder($order)->create();
        });

        $this->login($user);
        $route         = URL::route($this->routeName);
        $response      = $this->get($route);
        $data          = Collection::make($response->json('data'));
        $dataRouteKeys = $data->pluck(Order::keyName())->toArray();

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals($ordersRouteKeys, $dataRouteKeys);
    }

    /** @test */
    public function it_returns_the_correct_pubnub_channel_for_each_order()
    {
        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();
        Order::factory()->usingSupplier($supplier)->pending()->count(10)->create();
        $orders = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->count(5)->create();

        $orders->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $this->assertCount(5, $data);
        $data->each(function($rawOrder) {
            $this->assertStringContainsString($rawOrder['supplier']['id'], $rawOrder['channel']);
        });
    }
}

