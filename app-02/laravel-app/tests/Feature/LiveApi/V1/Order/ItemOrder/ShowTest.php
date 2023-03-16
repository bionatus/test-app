<?php

namespace Tests\Feature\LiveApi\V1\Order\ItemOrder;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Order\ItemOrderController;
use App\Http\Resources\LiveApi\V1\Order\ItemOrder\BaseResource;
use App\Models\AirFilter;
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

/** @see ItemOrderController */
class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_ITEM_ORDER_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $supplier  = Supplier::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->create();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::ITEM_ORDER => $itemOrder]));
    }

    /** @test */
    public function it_display_a_item_order_with_part()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->create();
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create();

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::JsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($data['id'], $itemOrder->getRouteKey());
    }

    /** @test */
    public function it_display_a_item_order_with_supply()
    {
        $staff     = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($staff->supplier)->create();
        $supply    = Supply::factory()->create();
        $itemOrder = ItemOrder::factory()
            ->usingOrder($order)
            ->usingItem($supply->item)
            ->create(['supply_detail' => 'detail test']);

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::JsonSchema(false), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertSame($data['id'], $itemOrder->getRouteKey());
    }
}
