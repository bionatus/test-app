<?php

namespace Tests\Feature\Api\V4\Account\Supplier\Order;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V4\Account\Supplier\OrderController;
use App\Http\Resources\Api\V4\Account\Supplier\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see OrderController */
class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V4_ACCOUNT_SUPPLIER_ORDER_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $supplier = Supplier::factory()->createQuietly();
        $this->withoutExceptionHandling();
        $route = URL::route($this->routeName, [RouteParameters::SUPPLIER => $supplier->uuid]);
        $this->expectException(UnauthorizedHttpException::class);

        $this->get($route);
    }

    /** @test */
    public function it_returns_a_list_of_orders_filtered_by_user_and_by_supplier()
    {
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        Order::factory()->usingSupplier($supplier)->pending()->count(3)->create();
        Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->count(2)->create();

        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $this->login($user);
        $route = URL::route($this->routeName, [RouteParameters::SUPPLIER => $supplier->uuid]);

        $response = $this->get($route);

        $data = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount(2, $data);
    }

    /** @test */
    public function it_returns_a_list_of_orders_sorted_by_status()
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

        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $orders          = Collection::make([
            $orderPendingApproval,
            $orderApproved,
            $orderPending,
            $orderCanceled,
            $orderCompleted,
        ]);
        $ordersRouteKeys = $orders->pluck(Order::routeKeyName())->toArray();

        $this->login($user);
        $route    = URL::route($this->routeName, [RouteParameters::SUPPLIER => $supplier->getRouteKey()]);
        $response = $this->get($route);

        $data          = Collection::make($response->json('data'));
        $dataRouteKeys = $data->pluck(Order::keyName())->toArray();

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals($ordersRouteKeys, $dataRouteKeys);
    }

    /** @test */
    public function it_returns_a_list_of_orders_ordered_by_created_at_when_the_orders_have_the_same_status()
    {
        $supplier   = Supplier::factory()->createQuietly();
        $user       = User::factory()->create();
        $createdAt  = Carbon::now();
        $orderOne   = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create([
            'created_at' => $createdAt->clone()->addMinutes(20),
        ]);
        $orderTwo   = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create([
            'created_at' => $createdAt->clone()->addMinutes(30),
        ]);
        $orderThree = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create([
            'created_at' => $createdAt->clone()->addMinutes(10),
        ]);

        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $orders          = Collection::make([
            $orderTwo,
            $orderOne,
            $orderThree,
        ]);
        $ordersRouteKeys = $orders->pluck(Order::routeKeyName())->toArray();

        $this->login($user);
        $route         = URL::route($this->routeName, [RouteParameters::SUPPLIER => $supplier->uuid]);
        $response      = $this->get($route);
        $data          = Collection::make($response->json('data'));
        $dataRouteKeys = $data->pluck(Order::keyName())->toArray();

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals($ordersRouteKeys, $dataRouteKeys);
    }

    /** @test */
    public function it_returns_a_list_of_orders_sorted_by_created_at_when_the_orders_have_pending_or_approved_status()
    {
        $supplier         = Supplier::factory()->createQuietly();
        $user             = User::factory()->create();
        $createdAt        = Carbon::now();
        $orderPendingOne  = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create([
            'created_at' => $createdAt->clone()->addMinutes(30),
        ]);
        $orderPendingTwo  = Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create([
            'created_at' => $createdAt->clone()->addMinutes(20),
        ]);
        $orderApprovedOne = Order::factory()->usingUser($user)->usingSupplier($supplier)->approved()->create([
            'created_at' => $createdAt->clone()->addMinutes(10),
        ]);
        $orderApprovedTwo = Order::factory()->usingUser($user)->usingSupplier($supplier)->approved()->create([
            'created_at' => $createdAt->clone()->addMinutes(40),
        ]);

        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $orders          = Collection::make([
            $orderApprovedTwo,
            $orderPendingOne,
            $orderPendingTwo,
            $orderApprovedOne,
        ]);
        $ordersRouteKeys = $orders->pluck(Order::routeKeyName())->toArray();

        $this->login($user);
        $route         = URL::route($this->routeName, [RouteParameters::SUPPLIER => $supplier->uuid]);
        $response      = $this->get($route);
        $data          = Collection::make($response->json('data'));
        $dataRouteKeys = $data->pluck(Order::keyName())->toArray();

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals($ordersRouteKeys, $dataRouteKeys);
    }

    /** @test */
    public function it_returns_a_list_of_orders_sorted_by_created_at_when_the_orders_have_completed_or_canceled_status()
    {
        $supplier          = Supplier::factory()->createQuietly();
        $user              = User::factory()->create();
        $createdAt         = Carbon::now();
        $orderCompletedOne = Order::factory()->usingUser($user)->usingSupplier($supplier)->completed()->create([
            'created_at' => $createdAt->clone()->addMinutes(10),
        ]);
        $orderCompletedTwo = Order::factory()->usingUser($user)->usingSupplier($supplier)->completed()->create([
            'created_at' => $createdAt->clone()->addMinutes(30),
        ]);
        $orderCanceledOne  = Order::factory()->usingUser($user)->usingSupplier($supplier)->canceled()->create([
            'created_at' => $createdAt->clone()->addMinutes(40),
        ]);
        $orderCanceledTwo  = Order::factory()->usingUser($user)->usingSupplier($supplier)->canceled()->create([
            'created_at' => $createdAt->clone()->addMinutes(20),
        ]);

        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $orders          = Collection::make([
            $orderCanceledOne,
            $orderCompletedTwo,
            $orderCanceledTwo,
            $orderCompletedOne,
        ]);
        $ordersRouteKeys = $orders->pluck(Order::routeKeyName())->toArray();

        $this->login($user);
        $route         = URL::route($this->routeName, [RouteParameters::SUPPLIER => $supplier->uuid]);
        $response      = $this->get($route);
        $data          = Collection::make($response->json('data'));
        $dataRouteKeys = $data->pluck(Order::keyName())->toArray();

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertEquals($ordersRouteKeys, $dataRouteKeys);
    }
}
