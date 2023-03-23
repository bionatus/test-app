<?php

namespace Tests\Feature\Api\V3\Order;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\OrderController;
use App\Http\Requests\Api\V3\Order\IndexRequest;
use App\Http\Resources\Api\V3\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Status;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\CanRefreshDatabase;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see OrderController */
class IndexTest extends TestCase
{
    use CanRefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V3_ORDER_INDEX;

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
    public function it_returns_a_list_of_orders_filtered_by_user()
    {
        $this->refreshDatabaseForSingleTest();
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        Order::factory()->usingSupplier($supplier)->pending()->count(3)->create();
        Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->count(2)->create();
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
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
    public function it_returns_a_list_of_orders_filtered_by_user_and_status()
    {
        $this->refreshDatabaseForSingleTest();
        $user           = User::factory()->create();
        $supplier       = Supplier::factory()->createQuietly();
        $expectedOrders = Order::factory()
            ->usingUser($user)
            ->usingSupplier($supplier)
            ->pendingApproval()
            ->count(2)
            ->create()
            ->sortByDesc('id');

        Order::factory()->usingUser($user)->usingSupplier($supplier)->count(3)->approved()->create();

        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $this->login($user);
        $route    = URL::route($this->routeName, [RequestKeys::STATUS => Status::STATUS_NAME_PENDING_APPROVAL]);
        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $this->assertCount(2, $data);
        $data->each(function(array $rawPart, int $index) use ($expectedOrders) {
            $order = $expectedOrders->shift();
            $this->assertSame($order->getRouteKey(), $rawPart['id']);
        });
    }

    /** @test */
    public function it_returns_a_list_of_orders_ordered_by_status()
    {
        $this->refreshDatabaseForSingleTest();
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
        $this->refreshDatabaseForSingleTest();
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

        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $orders          = Collection::make([
            $order2,
            $order1,
            $order3,
        ]);
        $ordersRouteKeys = $orders->pluck(Order::routeKeyName())->toArray();

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
        $this->refreshDatabaseForSingleTest();
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

        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $orders          = Collection::make([
            $orderApproved2,
            $orderPending1,
            $orderPending2,
            $orderApproved1,
        ]);
        $ordersRouteKeys = $orders->pluck(Order::routeKeyName())->toArray();

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
        $this->refreshDatabaseForSingleTest();
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

        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $orders          = Collection::make([
            $orderCanceled1,
            $orderCompleted2,
            $orderCanceled2,
            $orderCompleted1,
        ]);
        $ordersRouteKeys = $orders->pluck(Order::routeKeyName())->toArray();

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
        $this->refreshDatabaseForSingleTest();
        $supplier = Supplier::factory()->createQuietly();
        $user     = User::factory()->create();
        Order::factory()->count(10)->usingSupplier($supplier)->pending()->create();
        Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->count(5)->create();
        Order::query()->each(function(Order $order) {
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

