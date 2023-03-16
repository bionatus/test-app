<?php

namespace Tests\Feature\LiveApi\V1\Order\InProgress\ItemOrder;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Order\InProgress\ItemOrderController;
use App\Http\Resources\LiveApi\V1\Order\InProgress\ItemOrder\BaseResource;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Part;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ItemOrderController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_ITEM_ORDER_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $order = Order::factory()->createQuietly();

        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName, $order));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:read,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function if_order_is_not_canceled_it_displays_a_list_of_available_and_removed_order_items()
    {
        $supplier            = Supplier::factory()->withEmail()->createQuietly();
        $order               = Order::factory()->usingSupplier($supplier)->approved()->create();
        $availableParts      = Part::factory()->count(10)->create();
        $orderItemsAvailable = ItemOrder::factory()
            ->usingOrder($order)
            ->count(10)
            ->sequence(fn(Sequence $sequence) => ['item_id' => $availableParts->get($sequence->index)->item->getKey()])
            ->create(['status' => ItemOrder::STATUS_AVAILABLE]);

        $removedParts      = Part::factory()->count(2)->create();
        $orderItemsRemoved = ItemOrder::factory()
            ->usingOrder($order)
            ->count(2)
            ->sequence(fn(Sequence $sequence) => ['item_id' => $removedParts->get($sequence->index)->item->getKey()])
            ->create(['status' => ItemOrder::STATUS_REMOVED]);

        $unavailableParts = Part::factory()->count(3)->create();
        ItemOrder::factory()
            ->usingOrder($order)
            ->count(3)
            ->sequence(fn(Sequence $sequence
            ) => ['item_id' => $unavailableParts->get($sequence->index)->item->getKey()])
            ->create(['status' => ItemOrder::STATUS_NOT_AVAILABLE]);

        $seeBelowParts = Part::factory()->count(1)->create();
        ItemOrder::factory()
            ->usingOrder($order)
            ->count(1)
            ->sequence(fn(Sequence $sequence) => ['item_id' => $seeBelowParts->get($sequence->index)->item->getKey()])
            ->create(['status' => ItemOrder::STATUS_SEE_BELOW_ITEM]);

        $route = URL::route($this->routeName, $order);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());

        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data          = Collection::make($response->json('data'))->pluck('id')->toArray();
        $expectedItems = $orderItemsAvailable->merge($orderItemsRemoved)->values()->take(count($data));

        $this->assertEqualsCanonicalizing($expectedItems->pluck(ItemOrder::routeKeyName())->toArray(), $data);
    }

    /** @test */
    public function if_order_is_canceled_it_displays_a_list_of_all_order_items()
    {
        $supplier            = Supplier::factory()->withEmail()->createQuietly();
        $order               = Order::factory()->usingSupplier($supplier)->canceled()->create();
        $availableParts      = Part::factory()->count(10)->create();
        $orderItemsAvailable = ItemOrder::factory()
            ->usingOrder($order)
            ->count(10)
            ->sequence(fn(Sequence $sequence) => ['item_id' => $availableParts->get($sequence->index)->item->getKey()])
            ->create(['status' => ItemOrder::STATUS_AVAILABLE]);

        $removedParts      = Part::factory()->count(2)->create();
        $orderItemsRemoved = ItemOrder::factory()
            ->usingOrder($order)
            ->count(2)
            ->sequence(fn(Sequence $sequence) => ['item_id' => $removedParts->get($sequence->index)->item->getKey()])
            ->create(['status' => ItemOrder::STATUS_REMOVED]);

        $unavailableParts      = Part::factory()->count(3)->create();
        $orderItemsUnAvailable = ItemOrder::factory()
            ->usingOrder($order)
            ->count(3)
            ->sequence(fn(Sequence $sequence
            ) => ['item_id' => $unavailableParts->get($sequence->index)->item->getKey()])
            ->create(['status' => ItemOrder::STATUS_NOT_AVAILABLE]);

        $seeBelowParts      = Part::factory()->count(1)->create();
        $orderItemsSeeBelow = ItemOrder::factory()
            ->usingOrder($order)
            ->count(1)
            ->sequence(fn(Sequence $sequence) => ['item_id' => $seeBelowParts->get($sequence->index)->item->getKey()])
            ->create(['status' => ItemOrder::STATUS_SEE_BELOW_ITEM]);

        $pendingParts      = Part::factory()->count(1)->create();
        $orderItemsPending = ItemOrder::factory()
            ->usingOrder($order)
            ->count(1)
            ->sequence(fn(Sequence $sequence) => ['item_id' => $pendingParts->get($sequence->index)->item->getKey()])
            ->create(['status' => ItemOrder::STATUS_PENDING]);

        $route = URL::route($this->routeName, $order);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());

        $response = $this->get($route);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), true);
        $this->validateResponseSchema($schema, $response);

        $data          = Collection::make($response->json('data'))->pluck('id')->toArray();
        $expectedItems = $orderItemsAvailable->merge($orderItemsRemoved)
            ->merge($orderItemsUnAvailable)
            ->merge($orderItemsSeeBelow)
            ->merge($orderItemsPending)
            ->values()
            ->take(count($data));

        $this->assertEqualsCanonicalizing($expectedItems->pluck(ItemOrder::routeKeyName())->toArray(), $data);
    }
}
