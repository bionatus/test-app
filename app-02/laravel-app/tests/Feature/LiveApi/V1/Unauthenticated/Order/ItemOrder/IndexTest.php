<?php

namespace Tests\Feature\LiveApi\V1\Unauthenticated\Order\ItemOrder;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Unauthenticated\Order\ItemOrderController;
use App\Http\Resources\LiveApi\V1\Unauthenticated\Order\ItemOrder\BaseResource;
use App\Models\AirFilter;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Part;
use App\Models\Supply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see ItemOrderController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_UNAUTHENTICATED_ORDER_ITEM_ORDER_INDEX;

    /** @test */
    public function it_returns_a_list_of_available_order_items_when_order_is_not_cancelled()
    {
        $order      = Order::factory()->approved()->create();
        $itemOrders = Collection::make();
        $part       = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrders->push(ItemOrder::factory()
            ->usingOrder($order)
            ->usingItem($part->item)
            ->create(['status' => ItemOrder::STATUS_AVAILABLE]));

        $anotherPart = Part::factory()->create();
        AirFilter::factory()->usingPart($anotherPart)->create();
        $itemOrders->push(ItemOrder::factory()
            ->usingOrder($order)
            ->usingItem($anotherPart->item)
            ->create(['status' => ItemOrder::STATUS_AVAILABLE]));

        $supply = Supply::factory()->create();
        $itemOrders->push(ItemOrder::factory()
            ->usingOrder($order)
            ->usingItem($supply->item)
            ->create(['status' => ItemOrder::STATUS_AVAILABLE]));

        $lastAnotherPart = Part::factory()->create();
        AirFilter::factory()->usingPart($lastAnotherPart)->create();
        ItemOrder::factory()
            ->usingOrder($order)
            ->usingItem($lastAnotherPart->item)
            ->create(['status' => ItemOrder::STATUS_NOT_AVAILABLE]);

        $anotherSupply = Supply::factory()->create();
        ItemOrder::factory()
            ->usingOrder($order)
            ->usingItem($anotherSupply->item)
            ->create(['status' => ItemOrder::STATUS_SEE_BELOW_ITEM]);

        $partNotInOrder = Part::factory()->create();
        ItemOrder::factory()->usingItem($partNotInOrder->item)->createQuietly();

        $route = URL::route($this->routeName, [RouteParameters::UNAUTHENTICATED_ORDER => $order->getRouteKey()]);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);

        $data = Collection::make($response->json('data'));

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount(3, $data);

        $data->each(function(array $rawOrderItem, int $index) use ($itemOrders) {
            $itemOrder = $itemOrders->get($index);
            $this->assertSame($itemOrder->getRouteKey(), $rawOrderItem['id']);
        });
    }

    /** @test */
    public function it_returns_a_list_of_all_order_items_when_order_is_canceled()
    {
        $order      = Order::factory()->canceled()->create();
        $itemOrders = Collection::make();
        $part       = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrders->push(ItemOrder::factory()
            ->usingOrder($order)
            ->usingItem($part->item)
            ->create(['status' => ItemOrder::STATUS_NOT_AVAILABLE]));

        $anotherPart = Part::factory()->create();
        AirFilter::factory()->usingPart($anotherPart)->create();
        $itemOrders->push(ItemOrder::factory()
            ->usingOrder($order)
            ->usingItem($anotherPart->item)
            ->create(['status' => ItemOrder::STATUS_AVAILABLE]));

        $lastAnotherPart = Part::factory()->create();
        AirFilter::factory()->usingPart($lastAnotherPart)->create();
        $itemOrders->push(ItemOrder::factory()
            ->usingOrder($order)
            ->usingItem($lastAnotherPart->item)
            ->create(['status' => ItemOrder::STATUS_REMOVED]));

        $supply = Supply::factory()->create();
        $itemOrders->push(ItemOrder::factory()
            ->usingOrder($order)
            ->usingItem($supply->item)
            ->create(['status' => ItemOrder::STATUS_SEE_BELOW_ITEM]));

        $route = URL::route($this->routeName, [RouteParameters::UNAUTHENTICATED_ORDER => $order->getRouteKey()]);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);

        $data = Collection::make($response->json('data'));

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $this->assertCount(4, $data);

        $data->each(function(array $rawOrderItem, int $index) use ($itemOrders) {
            $itemOrder = $itemOrders->get($index);
            $this->assertSame($itemOrder->getRouteKey(), $rawOrderItem['id']);
        });
    }
}
