<?php

namespace Tests\Feature\LiveApi\V1\Order\InProgress;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\LiveApi\V1\Order\InProgressController;
use App\Http\Resources\LiveApi\V1\Order\BaseResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\User;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see InProgressController */
class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_displays_a_list_of_approved_completed_and_canceled_orders()
    {
        $supplier      = Supplier::factory()->withEmail()->createQuietly();
        $otherSupplier = Supplier::factory()->createQuietly();
        Order::factory()->count(4)->usingSupplier($otherSupplier)->pending()->create();

        Order::factory()->count(3)->usingSupplier($supplier)->pendingApproval()->create();
        $ordersApproved  = Order::factory()->count(4)->usingSupplier($supplier)->approved()->create();
        $ordersCompleted = Order::factory()->count(5)->usingSupplier($supplier)->completed()->create();
        $ordersCanceled  = Order::factory()->count(6)->usingSupplier($supplier)->canceled()->create();
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $route = URL::route($this->routeName);

        $orders = $ordersApproved->merge($ordersCompleted)->merge($ordersCanceled);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());

        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data           = Collection::make($response->json('data'))->pluck('id')->toArray();
        $expectedOrders = $orders->values()->take(count($data));

        $this->assertEqualsCanonicalizing($expectedOrders->pluck(Order::routeKeyName())->toArray(), $data);
    }

    /** @test */
    public function it_displays_a_list_of_orders_sorted_by_status()
    {
        $supplier = Supplier::factory()->withEmail()->createQuietly();

        $ordersCompleted = Order::factory()->usingSupplier($supplier)->count(5)->completed()->create();
        $ordersCompleted->each(function(Order &$order, int $index) {
            $order->updated_at = Carbon::now()->subSeconds($index * 6);
            $order->save();
        });

        $ordersApproved = Order::factory()->usingSupplier($supplier)->count(5)->approved()->create();
        $ordersApproved->each(function(Order &$order, int $index) {
            $order->updated_at = Carbon::now()->subSeconds($index);
            $order->save();
        });

        $ordersCanceled = Order::factory()->usingSupplier($supplier)->count(5)->canceled()->create();
        $ordersCanceled->each(function(Order &$order, int $index) {
            $order->updated_at = Carbon::now()->subSeconds($index * 7);
            $order->save();
        });

        Order::factory()->usingSupplier($supplier)->sequence(fn($sequence) => [
            'updated_at' => Carbon::now()->subSeconds($sequence->index),
        ])->count(5)->pendingApproval()->create();

        Order::factory()->usingSupplier($supplier)->sequence(fn($sequence) => [
            'updated_at' => Carbon::now()->subSeconds($sequence->index),
        ])->count(5)->pending()->create();

        $orders          = $ordersApproved->concat($ordersCompleted->concat($ordersCanceled));
        $ordersRouteKeys = $orders->pluck(Order::routeKeyName())->toArray();

        $route = URL::route($this->routeName);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data          = Collection::make($response->json('data'));
        $dataRouteKeys = $data->pluck('id')->toArray();
        $this->assertEquals($ordersRouteKeys, $dataRouteKeys);
    }

    /** @test */
    public function it_displays_approved_completed_and_canceled_orders_sort_by_update_date()
    {
        $supplier = Supplier::factory()->withEmail()->createQuietly();
        $updatedAt = Carbon::now();

        Carbon::setTestNow($updatedAt->clone()->subDays(3));
        $order1             = Order::factory()->usingSupplier($supplier)->completed()->create([
            'created_at' => Carbon::now()->subDays(4),
        ]);

        Carbon::setTestNow($updatedAt->clone()->subDays(2));
        $order2             = Order::factory()->usingSupplier($supplier)->completed()->create([
            'created_at' => Carbon::now()->subDays(3),
        ]);

        Carbon::setTestNow($updatedAt->clone()->subDays(3));
        $order3             = Order::factory()->usingSupplier($supplier)->canceled()->create([
            'created_at' => Carbon::now()->subDays(2),
        ]);

        Carbon::setTestNow($updatedAt->clone()->subDays(1));
        $order4             = Order::factory()->usingSupplier($supplier)->canceled()->create([
            'created_at' => Carbon::now()->subDays(1),
        ]);

        Carbon::setTestNow(Carbon::now());
        $order5             = Order::factory()->usingSupplier($supplier)->approved()->create([
            'created_at' => Carbon::now()->subDays(1),
        ]);

        Carbon::setTestNow($updatedAt->clone()->subDays(3));
        $order6             = Order::factory()->usingSupplier($supplier)->approved()->create([
            'created_at' => Carbon::now()->subDays(1),
        ]);

        $orders = Collection::make([
            $order5,
            $order6,
            $order2,
            $order1,
            $order4,
            $order3,
        ]);

        $ordersRouteKeys = $orders->pluck(Order::routeKeyName())->toArray();

        $route = URL::route($this->routeName);

        Carbon::setTestNow(Carbon::now());
        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());
        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data          = Collection::make($response->json('data'));
        $dataRouteKeys = $data->pluck('id')->toArray();
        $this->assertEquals($ordersRouteKeys, $dataRouteKeys);
    }

    /** @test */
    public function it_displays_a_list_of_orders_filtered_by_search_string()
    {
        $supplier  = Supplier::factory()->withEmail()->createQuietly();
        $user      = User::factory()->create();
        $otherUser = User::factory()->create(['first_name' => 'User SEARCH first name']);
        $lastUser  = User::factory()->create(['last_name' => 'User SEARCH last name']);
        $company   = Company::factory()->create(['name' => 'Company SEARCH name']);

        $orders = new Collection();
        CompanyUser::factory()->usingUser($user)->usingCompany($company)->create();
        $orders->push(Order::factory()->approved()->usingSupplier($supplier)->usingUser($user)->create());
        $orders->push(Order::factory()->approved()->usingSupplier($supplier)->usingUser($otherUser)->create());
        $orders->push(Order::factory()->approved()->usingSupplier($supplier)->usingUser($lastUser)->create());
        $orders->push(Order::factory()->approved()->usingSupplier($supplier)->create(['name' => 'Order SEARCH name']));
        $orders->push(Order::factory()
            ->approved()
            ->usingSupplier($supplier)
            ->create(['bid_number' => 'Order SEARCH bid_number']));
        Order::factory()->usingSupplier($supplier)->pending()->count(10)->create();
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        $route = URL::route($this->routeName, [RequestKeys::SEARCH_STRING => 'SEARCH']);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());

        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data = Collection::make($response->json('data'))->pluck('id')->toArray();

        $this->assertEqualsCanonicalizing($orders->pluck(Order::routeKeyName())->toArray(), $data);
    }
}
