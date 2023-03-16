<?php

namespace Tests\Feature\LiveApi\V1\Order\CustomItem;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Order\CustomItemController;
use App\Http\Requests\LiveApi\V1\Order\CustomItem\StoreRequest;
use App\Http\Resources\LiveApi\V1\Order\ItemOrder\BaseResource;
use App\Models\CustomItem;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Scopes\ByName;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see CustomItemController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_CUSTOM_ITEM_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $supplier = Supplier::factory()->createQuietly();
        $this->post(URL::route($this->routeName,
            [RouteParameters::ORDER => Order::factory()->usingSupplier($supplier)->create()->getRouteKey()]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:update,' . RouteParameters::ORDER]);
        $this->assertRouteUsesMiddleware($this->routeName, ['can:createCustomItem,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_creates_a_custom_item()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->pending()->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);
        $name     = 'Custom item name';

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [
            RequestKeys::NAME => $name,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(CustomItem::tableName(), [
            'name'         => $name,
            'creator_id'   => $staff->supplier->getKey(),
            'creator_type' => Supplier::MORPH_ALIAS,
        ]);

        $customItem = CustomItem::scoped(new ByName($name))->first();

        $this->assertDatabaseHas(Item::tableName(), ['id' => $customItem->getKey()]);
        $this->assertDatabaseHas(ItemOrder::tableName(),
            ['order_id' => $order->getKey(), 'item_id' => $customItem->getKey()]);
    }

    /** @test */
    public function it_creates_a_custom_item_with_initially_item_orders_with_quantity_and_quantity_requested_in_zero()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->pending()->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);
        $name     = 'Custom item name';

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [
            RequestKeys::NAME => $name,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(CustomItem::tableName(), [
            'name'         => $name,
            'creator_id'   => $staff->supplier->getKey(),
            'creator_type' => Supplier::MORPH_ALIAS,
        ]);

        $customItem = CustomItem::query()->scoped(new ByName($name))->first();

        $this->assertDatabaseHas(Item::tableName(), ['id' => $customItem->getKey()]);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'order_id'           => $order->getKey(),
            'item_id'            => $customItem->getKey(),
            'quantity'           => 0,
            'quantity_requested' => 0,
        ]);
    }

    /** @test */
    public function it_creates_only_10_custom_item()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->pending()->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);

        $customItems = CustomItem::factory()->count(10)->create();
        ItemOrder::factory()->usingOrder($order)->count(10)->sequence(fn(Sequence $sequence
        ) => ['item_id' => $customItems->get($sequence->index)->item->getKey()])->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $name     = 'Eleven custom item';
        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [
            RequestKeys::NAME => $name,
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseMissing(CustomItem::tableName(), ['name' => $name]);
    }
}
