<?php

namespace Tests\Feature\LiveApi\V1\Order\ItemOrder;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Order\ItemOrderController;
use App\Http\Resources\LiveApi\V1\Order\ItemOrder\BaseResource;
use App\Models\AirFilter;
use App\Models\CustomItem;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Part;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\Supply;
use Auth;
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

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_ITEM_ORDER_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

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
    public function it_displays_a_list_of_order_items()
    {
        $staff      = Staff::factory()->createQuietly();
        $supplier   = $staff->supplier;
        $order      = Order::factory()->usingSupplier($supplier)->create();
        $parts      = Part::factory()->count(5)->create();
        $orderItems = Collection::make();

        $parts->each(function(Part $part) use ($order, &$orderItems) {
            AirFilter::factory()->usingPart($part)->create();
            $orderItems->push(ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create());
        });

        $supplies = Supply::factory()->visible()->count(3)->create();
        $supplies->each(function(Supply $supply) use ($order, &$orderItems) {
            $orderItems->push(ItemOrder::factory()->usingOrder($order)->usingItem($supply->item)->create());
        });

        $customItems = CustomItem::factory()->count(2)->create();
        $customItems->each(function(CustomItem $customItem) use ($order, &$orderItems) {
            $orderItems->push(ItemOrder::factory()->usingOrder($order)->usingItem($customItem->item)->create());
        });

        $route = URL::route($this->routeName, $order);

        Auth::shouldUse('live');
        $this->login($staff);
        $response = $this->get($route);
        $data     = Collection::make($response->json('data'));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount(10, $data);

        $data->each(function(array $rawItem, int $index) use ($orderItems) {
            $orderItem = $orderItems->get($index);
            $this->assertSame($orderItem->getRouteKey(), $rawItem['id']);
        });
    }
}
