<?php

namespace Tests\Feature\LiveApi\V1\Order\ItemOrder;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Order\ItemOrderController;
use App\Http\Requests\LiveApi\V1\Order\ItemOrder\UpdateRequest;
use App\Http\Resources\LiveApi\V1\Order\ItemOrder\BaseResource;
use App\Models\AirFilter;
use App\Models\CustomItem;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Part;
use App\Models\Replacement;
use App\Models\SingleReplacement;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\Supply;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ItemOrderController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_ITEM_ORDER_UPDATE;

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
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:update,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, UpdateRequest::class);
    }

    /** @test */
    public function it_updates_the_item_order_that_uses_a_part()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create();

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $quantity = 2;
        $price    = 1234.20;
        $status   = ItemOrder::STATUS_AVAILABLE;

        $response = $this->patch($route, [
            RequestKeys::QUANTITY    => $quantity,
            RequestKeys::PRICE       => $price,
            RequestKeys::STATUS      => $status,
            RequestKeys::REPLACEMENT => null,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'order_id'                 => $order->getKey(),
            'item_id'                  => $part->item->getKey(),
            'quantity'                 => $quantity,
            'price'                    => 123420,
            'status'                   => $status,
            'replacement_id'           => null,
            'generic_part_description' => null,
            'supply_detail'            => null,
        ]);
    }

    /** @test */
    public function it_updates_the_item_order_that_uses_a_part_with_a_generic_replacement()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create();

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $quantity    = 3;
        $price       = 44.23;
        $status      = ItemOrder::STATUS_AVAILABLE;
        $description = 'fake generic part';

        $response = $this->patch($route, [
            RequestKeys::QUANTITY    => $quantity,
            RequestKeys::PRICE       => $price,
            RequestKeys::STATUS      => $status,
            RequestKeys::REPLACEMENT => ['type' => 'generic', 'description' => $description],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'order_id'                 => $order->getKey(),
            'item_id'                  => $part->item->getKey(),
            'quantity'                 => $quantity,
            'price'                    => 4423,
            'status'                   => $status,
            'replacement_id'           => null,
            'generic_part_description' => $description,
            'supply_detail'            => null,
        ]);
    }

    /** @test */
    public function it_updates_the_item_order_that_uses_a_part_with_a_part_replacement()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $replacement       = Replacement::factory()->usingPart($part)->create();
        $singleReplacement = SingleReplacement::factory()->usingReplacement($replacement)->create();
        $itemOrder         = ItemOrder::factory()
            ->usingOrder($order)
            ->usingItem($part->item)
            ->usingReplacement($singleReplacement->replacement)
            ->create();

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $quantity = 1;
        $price    = 1244.56;
        $status   = ItemOrder::STATUS_AVAILABLE;
        $id       = $singleReplacement->replacement->uuid;

        $response = $this->patch($route, [
            RequestKeys::QUANTITY    => $quantity,
            RequestKeys::PRICE       => $price,
            RequestKeys::STATUS      => $status,
            RequestKeys::REPLACEMENT => ['type' => 'replacement', 'id' => $id],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'order_id'                 => $order->getKey(),
            'item_id'                  => $part->item->getKey(),
            'quantity'                 => $quantity,
            'price'                    => 124456,
            'status'                   => $status,
            'replacement_id'           => $replacement->getKey(),
            'generic_part_description' => null,
            'supply_detail'            => null,
        ]);
    }

    /** @test */
    public function it_updates_the_item_order_that_uses_a_supply()
    {
        $staff     = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($staff->supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        $supply    = Supply::factory()->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($supply->item)->create();

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $quantity     = 1;
        $price        = 0;
        $status       = ItemOrder::STATUS_NOT_AVAILABLE;
        $supplyDetail = 'Fake detail';

        $response = $this->patch($route, [
            RequestKeys::QUANTITY      => $quantity,
            RequestKeys::PRICE         => $price,
            RequestKeys::STATUS        => $status,
            RequestKeys::SUPPLY_DETAIL => $supplyDetail,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(false)), $response);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'order_id'                 => $order->getKey(),
            'item_id'                  => $supply->item->getKey(),
            'quantity'                 => $quantity,
            'price'                    => $price,
            'status'                   => $status,
            'replacement_id'           => null,
            'generic_part_description' => null,
            'supply_detail'            => $supplyDetail,
        ]);
    }

    /** @test */
    public function it_set_the_item_order_status_to_available_if_was_pending()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->create();

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $quantity = 2;
        $price    = 0;

        $response = $this->patch($route, [
            RequestKeys::QUANTITY    => $quantity,
            RequestKeys::PRICE       => $price,
            RequestKeys::STATUS      => ItemOrder::STATUS_PENDING,
            RequestKeys::REPLACEMENT => null,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'order_id'                 => $order->getKey(),
            'item_id'                  => $part->item->getKey(),
            'quantity'                 => $quantity,
            'price'                    => $price,
            'status'                   => ItemOrder::STATUS_AVAILABLE,
            'replacement_id'           => null,
            'generic_part_description' => null,
            'supply_detail'            => null,
        ]);
    }

    /** @test */
    public function it_updates_the_item_order_that_uses_a_custom_item()
    {
        $staff      = Staff::factory()->createQuietly();
        $order      = Order::factory()->usingSupplier($staff->supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        $customItem = CustomItem::factory()->create();
        $itemOrder  = ItemOrder::factory()->usingOrder($order)->usingItem($customItem->item)->create();

        $route = URL::route($this->routeName,
            [RouteParameters::ORDER => $order, RouteParameters::ITEM_ORDER => $itemOrder]);

        Auth::shouldUse('live');
        $this->login($staff);

        $quantity     = 1;
        $price        = 0;
        $status       = ItemOrder::STATUS_AVAILABLE;
        $customDetail = 'Fake detail';

        $response = $this->patch($route, [
            RequestKeys::QUANTITY      => $quantity,
            RequestKeys::PRICE         => $price,
            RequestKeys::STATUS        => $status,
            RequestKeys::CUSTOM_DETAIL => $customDetail,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(false)), $response);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'order_id'                 => $order->getKey(),
            'item_id'                  => $customItem->item->getKey(),
            'quantity'                 => $quantity,
            'price'                    => $price,
            'status'                   => $status,
            'replacement_id'           => null,
            'generic_part_description' => null,
            'supply_detail'            => null,
            'custom_detail'            => $customDetail,
        ]);
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_updates_the_item_order_status_without_extra_data_for_specified_statuses($status)
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->pending()->create(['working_on_it' => 'John Doe']);
        $part  = Part::factory()->create();
        AirFilter::factory()->usingPart($part)->create();
        $itemOrder = ItemOrder::factory()->usingOrder($order)->usingItem($part->item)->available()->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $route = URL::route($this->routeName, [
            RouteParameters::ORDER      => $order,
            RouteParameters::ITEM_ORDER => $itemOrder,
        ]);

        $response = $this->patch($route, [RequestKeys::STATUS => $status]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(false)), $response);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'order_id'                 => $order->getKey(),
            'item_id'                  => $part->item->getKey(),
            'quantity'                 => $itemOrder->quantity,
            'price'                    => null,
            'status'                   => $status,
            'replacement_id'           => null,
            'generic_part_description' => null,
            'supply_detail'            => null,
            'custom_detail'            => null,
        ]);
    }

    public function dataProvider()
    {
        return [[ItemOrder::STATUS_NOT_AVAILABLE], [ItemOrder::STATUS_PENDING]];
    }
}
