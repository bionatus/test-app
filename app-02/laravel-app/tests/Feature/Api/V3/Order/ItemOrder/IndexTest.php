<?php

namespace Tests\Feature\Api\V3\Order\ItemOrder;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Order\ItemOrderController;
use App\Http\Resources\Api\V3\Order\ItemOrder\BaseResource;
use App\Models\AirFilter;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Part;
use App\Models\Supplier;
use App\Models\Supply;
use App\Models\User;
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

    private string $routeName = RouteNames::API_V3_ORDER_ITEM_ORDER_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $supplier = Supplier::factory()->createQuietly();
        $this->get(URL::route($this->routeName,
            [RouteParameters::ORDER => Order::factory()->usingSupplier($supplier)->create()->getRouteKey()]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:read,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_returns_a_list_of_order_items()
    {
        $user       = User::factory()->create();
        $supplier   = Supplier::factory()->createQuietly();
        $order      = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
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

        $partNotInOrder = Part::factory()->create();
        ItemOrder::factory()
            ->usingItem($partNotInOrder->item)
            ->createQuietly(['status' => ItemOrder::STATUS_AVAILABLE]);

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]);
        $this->login($user);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);

        $data = Collection::make($response->json('data'));
        $this->assertCount(3, $data);

        $data->each(function(array $rawOrderItem, int $index) use ($itemOrders) {
            $itemOrder = $itemOrders->get($index);
            $this->assertSame($itemOrder->getRouteKey(), $rawOrderItem['id']);
        });
    }
}
