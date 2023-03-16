<?php

namespace Tests\Unit\Http\Requests\LiveApi\V2\Order\InProgress\ItemOrder\ExtraItem;

use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V2\Order\InProgress\ItemOrder\ExtraItemController;
use App\Http\Requests\LiveApi\V2\Order\InProgress\ItemOrder\ExtraItem\UpdateRequest;
use App\Models\CustomItem;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Route;
use Tests\Unit\Http\Requests\RequestTestCase;
use URL;

/** @see ExtraItemController */
class UpdateRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string  $requestClass = UpdateRequest::class;
    private string    $route;
    private ItemOrder $itemOrder;
    private Order     $order;

    protected function setUp(): void
    {
        parent::setUp();

        $supplier        = Supplier::factory()->createQuietly();
        $this->order     = Order::factory()->usingSupplier($supplier)->create();
        $this->itemOrder = ItemOrder::factory()
            ->usingOrder($this->order)
            ->usingItem(Item::factory()->create())
            ->notInitialRequest()
            ->create();

        Route::model('order', Order::class);

        $this->route = URL::route(LiveApiV2::LIVE_API_V2_ORDER_ITEM_ORDER_EXTRA_ITEM_UPDATE, ['order' => $this->order]);
    }

    /** @test */
    public function it_should_authorize()
    {
        $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey())
            ->assertAuthorized();
    }

    /** @test */
    public function its_items_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'patch', 'route' => $this->route]);

        $requestKey = RequestKeys::ITEMS;
        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS]);

        $request->assertValidationMessages([
            Lang::get('validation.required', [
                'attribute' => $this->getDisplayableAttribute($requestKey),
            ]),
        ]);
    }

    /** @test */
    public function its_items_orders_parameter_must_be_an_array()
    {
        $requestKey = RequestKeys::ITEMS;
        $request    = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => 'string item'],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();

        $request->assertValidationErrors([RequestKeys::ITEMS]);
        $request->assertValidationMessages([
            Lang::get('validation.array', ['attribute' => $this->getDisplayableAttribute($requestKey)]),
        ]);
    }

    /** @test */
    public function each_item_in_items_is_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [[]]],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0']);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::ITEMS . '.0']),
        ]);
    }

    /** @test */
    public function each_item_uuid_in_items_is_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [[]]],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.uuid']);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::ITEMS . '.0.uuid']),
        ]);
    }

    /** @test */
    public function each_item_uuid_in_items_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [['uuid' => 1]]],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.uuid']);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => RequestKeys::ITEMS . '.0.uuid']),
        ]);
    }

    /** @test */
    public function each_item_uuid_in_items_must_exist()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [['uuid' => 'invalid']]],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.uuid']);
        $request->assertValidationMessages(['Each item in item orders must exist. They must belong to an order and must not have been added in the initial request.']);
    }

    /** @test */
    public function each_item_uuid_in_items_must_belong_to_the_order()
    {
        $supplier  = Supplier::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        $itemOrder = ItemOrder::factory()->create();

        $route = URL::route(LiveApiV2::LIVE_API_V2_ORDER_ITEM_ORDER_EXTRA_ITEM_UPDATE, ['order' => $order]);

        $request = $this->formRequest($this->requestClass,
            [RequestKeys::ITEMS => [['uuid' => $itemOrder->getRouteKey()]]], ['method' => 'patch', 'route' => $route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.uuid']);
        $request->assertValidationMessages(['Each item in item orders must exist. They must belong to an order and must not have been added in the initial request.']);
    }

    /** @test */
    public function each_item_uuid_in_items_must_have_not_been_added_on_the_initial_request()
    {
        $supplier  = Supplier::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->create();

        $route = URL::route(LiveApiV2::LIVE_API_V2_ORDER_ITEM_ORDER_EXTRA_ITEM_UPDATE, ['order' => $order]);

        $request = $this->formRequest($this->requestClass,
            [RequestKeys::ITEMS => [['uuid' => $itemOrder->getRouteKey()]]], ['method' => 'patch', 'route' => $route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.uuid']);
        $request->assertValidationMessages(['Each item in item orders must exist. They must belong to an order and must not have been added in the initial request.']);
    }

    /** @test */
    public function each_item_uuid_must_belong_to_an_item_of_supply_type()
    {
        $itemOrderPart = ItemOrder::factory()
            ->usingOrder($this->order)
            ->usingItem(Item::factory()->part()->create())
            ->create();
        $request       = $this->formRequest($this->requestClass, [
            RequestKeys::ITEMS => [
                ['uuid' => $this->itemOrder->getRouteKey(), 'quantity' => 1],
                ['uuid' => $itemOrderPart->getRouteKey(), 'quantity' => 2],
            ],
        ], ['method' => 'patch', 'route' => $this->route]);
        $request->assertValidationFailed();

        $request->assertValidationErrors([RequestKeys::ITEMS . '.1.uuid']);
        $request->assertValidationMessages(['The item should be type supply or custom item added by the technician.']);
    }

    /** @test */
    public function its_item_uuid_must_belong_to_an_item_added_by_tech()
    {
        $itemOrderPart = ItemOrder::factory()
            ->usingOrder($this->order)
            ->usingItem(Item::factory()->part()->create())
            ->create();

        $customItemsUser = CustomItem::factory()->create();
        $itemOrder       = ItemOrder::factory()->usingItem($customItemsUser->item)->usingOrder($this->order)->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::ITEMS => [
                ['uuid' => $itemOrder->getRouteKey(), 'quantity' => 1],
                ['uuid' => $itemOrderPart->getRouteKey(), 'quantity' => 2],
            ],
        ], ['method' => 'patch', 'route' => $this->route]);
        $request->assertValidationFailed();

        $request->assertValidationErrors([RequestKeys::ITEMS . '.1.uuid']);
        $request->assertValidationMessages(['The item should be type supply or custom item added by the technician.']);
    }

    /** @test */
    public function each_item_quantity_in_items_is_required()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [[]]],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.quantity']);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => RequestKeys::ITEMS . '.0.quantity']),
        ]);
    }

    /** @test */
    public function each_item_quantity_in_items_must_be_a_integer()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [['quantity' => 'invalid']]],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.quantity']);
        $request->assertValidationMessages([
            Lang::get('validation.integer', ['attribute' => RequestKeys::ITEMS . '.0.quantity']),
        ]);
    }

    /** @test */
    public function each_item_quantity_in_items_must_be_grater_than_or_equal_to_0()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::ITEMS => [['quantity' => -1]]],
            ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.quantity']);
        $request->assertValidationMessages([
            Lang::get('validation.min.numeric', ['attribute' => RequestKeys::ITEMS . '.0.quantity', 'min' => 0]),
        ]);
    }

    /** @test */
    public function each_item_quantity_in_items_must_be_valid()
    {
        $this->itemOrder->quantity           = 5;
        $this->itemOrder->quantity_requested = 5;
        $this->itemOrder->save();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::ITEMS => [
                [
                    'uuid'     => $this->itemOrder->getRouteKey(),
                    'quantity' => 10,
                ],
            ],
        ], ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::ITEMS . '.0.quantity']);
        $request->assertValidationMessages(['Invalid quantity.']);
    }

    /** @test */
    public function it_passes_on_valid_values_with_type_supply()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::ITEMS => [
                ['uuid' => $this->itemOrder->getRouteKey(), 'quantity' => 1],
            ],
        ], ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationPassed();
    }

    /** @test */
    public function it_passes_on_valid_values_with_user_custom_item_type()
    {
        $customItemsUser = CustomItem::factory()->create();
        $itemOrder       = ItemOrder::factory()
            ->usingItem($customItemsUser->item)
            ->usingOrder($this->order)
            ->notInitialRequest()
            ->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::ITEMS => [
                ['uuid' => $itemOrder->getRouteKey(), 'quantity' => 1],
            ],
        ], ['method' => 'patch', 'route' => $this->route]);

        $request->assertValidationPassed();
    }
}
