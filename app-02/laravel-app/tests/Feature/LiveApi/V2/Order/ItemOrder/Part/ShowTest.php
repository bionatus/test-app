<?php

namespace Tests\Feature\LiveApi\V2\Order\ItemOrder\Part;

use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V2\Order\ItemOrder\PartController;
use App\Http\Resources\LiveApi\V2\Order\ItemOrder\Part\BaseResource;
use App\Models\AirFilter;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Part;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\Supply;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see PartController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_ITEM_ORDER_PART_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $supplier  = Supplier::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        $item      = Item::factory()->part()->create();
        $itemOrder = ItemOrder::factory()->usingItem($item)->usingOrder($order)->create();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]));
    }

    /** @test */
    public function it_display_an_item_order_part()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->create();
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create();

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::JsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($data['id'], $itemOrder->getRouteKey());
    }

    /** @test */
    public function it_does_not_display_a_item_order_with_supply()
    {
        $staff     = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($staff->supplier)->create();
        $supply    = Supply::factory()->visible()->create();
        $itemOrder = ItemOrder::factory()
            ->usingOrder($order)
            ->usingItem($supply->item)
            ->create(['supply_detail' => 'detail test']);

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function it_should_not_display_an_item_order_part_of_another_order()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->create();
        $anotherOrder = Order::factory()->usingSupplier($staff->supplier)->create();
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($anotherOrder)->usingItem($part->item)->create();

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::PART_ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
