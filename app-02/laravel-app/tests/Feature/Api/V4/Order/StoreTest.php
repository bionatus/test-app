<?php

namespace Tests\Feature\Api\V4\Order;

use App;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Events\Order\Created;
use App\Http\Controllers\Api\V4\OrderController;
use App\Http\Requests\Api\V4\Order\StoreRequest;
use App\Http\Resources\Api\V4\Order\BaseResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartOrder;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Oem;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\PubnubChannel;
use App\Models\Scopes\ByUuid;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\SupplierHour;
use App\Models\SupplierUser;
use App\Models\User;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use PubNub\PubNub;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see OrderController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V4_ORDER_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_returns_the_correct_base_resource_schema()
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $cart     = Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();
        CartItem::factory()->usingCart($cart)->create();

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
    }

    /** @test */
    public function it_stores_a_company_account_order_and_the_item_based_on_the_cart()
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $cart     = Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $oem      = Oem::factory()->create();
        $item1    = Item::factory()->create();
        $item2    = Item::factory()->create();

        $quantity1 = 1;
        $quantity2 = 2;

        CartItem::factory()->usingCart($cart)->usingItem($item1)->create(['quantity' => $quantity1]);
        CartItem::factory()->usingCart($cart)->usingItem($item2)->create(['quantity' => $quantity2]);

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::OEM => $oem->getRouteKey(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $data = Collection::make($response->json('data'));

        $this->assertDatabaseCount(Order::tableName(), 1);
        $this->assertDatabaseHas(Order::tableName(), [
            Order::routeKeyName() => $data->get('id'),
            'oem_id'              => $oem->getKey(),
            'supplier_id'         => $supplier->getKey(),
        ]);

        $this->assertDatabaseCount(ItemOrder::tableName(), 2);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'item_id'  => $item1->getKey(),
            'quantity' => $quantity1,
        ]);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'item_id'  => $item2->getKey(),
            'quantity' => $quantity2,
        ]);
    }

    /** @test */
    public function it_stores_a_personal_use_order_and_the_item_based_on_the_cart()
    {
        Event::fake(Created::class);

        $user    = User::factory()->create();
        $company = Company::factory()->create();
        CompanyUser::factory()->usingUser($user)->usingCompany($company)->create();
        $supplier = Supplier::factory()->createQuietly();
        $cart     = Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $oem      = Oem::factory()->create();
        $item1    = Item::factory()->create();
        $item2    = Item::factory()->create();

        $quantity1 = 1;
        $quantity2 = 2;

        CartItem::factory()->usingCart($cart)->usingItem($item1)->create(['quantity' => $quantity1]);
        CartItem::factory()->usingCart($cart)->usingItem($item2)->create(['quantity' => $quantity2]);

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::OEM     => $oem->getRouteKey(),
            RequestKeys::COMPANY => $company->getRouteKey(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $data = Collection::make($response->json('data'));

        $this->assertDatabaseCount(Order::tableName(), 1);
        $this->assertDatabaseHas(Order::tableName(), [
            Order::routeKeyName() => $data->get('id'),
            'oem_id'              => $oem->getKey(),
            'supplier_id'         => $supplier->getKey(),
            'company_id'          => $company->getKey(),
        ]);

        $this->assertDatabaseCount(ItemOrder::tableName(), 2);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'item_id'  => $item1->getKey(),
            'quantity' => $quantity1,
        ]);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'item_id'  => $item2->getKey(),
            'quantity' => $quantity2,
        ]);
    }

    /** @test */
    public function it_stores_an_order_with_order_substatus()
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $cart     = Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();
        CartItem::factory()->usingCart($cart)->create();
        $substatusId = 100;

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);
        $data = Collection::make($response->json('data'));

        $storedOrder = Order::where('uuid', $data->get('id'))->first();

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $storedOrder->getKey(),
            'substatus_id' => $substatusId,
        ]);
    }

    /** @test */
    public function it_stores_an_order_and_item_orders_with_the_same_date_created()
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $cart     = Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $item1    = Item::factory()->create();
        $item2    = Item::factory()->create();

        CartItem::factory()->usingCart($cart)->usingItem($item1)->create(['quantity' => 1]);
        CartItem::factory()->usingCart($cart)->usingItem($item2)->create(['quantity' => 3]);

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::OEM => null,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $data = Collection::make($response->json('data'));

        $this->assertDatabaseCount(Order::tableName(), 1);
        $this->assertDatabaseHas(Order::tableName(), [
            Order::routeKeyName() => $data->get('id'),
            'oem_id'              => null,
            'supplier_id'         => $supplier->getKey(),
        ]);
        $order = Order::scoped(new ByUuid($data->get('id')))->first();
        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_PENDING_REQUESTED,
        ]);

        $this->assertDatabaseCount(ItemOrder::tableName(), 2);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'order_id'   => $order->getKey(),
            'created_at' => $order->created_at,
        ]);
    }

    /** @test */
    public function it_stores_initially_item_orders_with_same_quantity_and_quantity_requested()
    {
        Event::fake(Created::class);

        $user      = User::factory()->create();
        $supplier  = Supplier::factory()->createQuietly();
        $cart      = Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $item1     = Item::factory()->create();
        $item2     = Item::factory()->create();
        $quantity1 = 1;
        $quantity2 = 3;

        CartItem::factory()->usingCart($cart)->usingItem($item1)->create(['quantity' => $quantity1]);
        CartItem::factory()->usingCart($cart)->usingItem($item2)->create(['quantity' => $quantity2]);

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::OEM => null,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $data = Collection::make($response->json('data'));

        $this->assertDatabaseCount(Order::tableName(), 1);
        $this->assertDatabaseHas(Order::tableName(), [
            Order::routeKeyName() => $data->get('id'),
            'oem_id'              => null,
            'supplier_id'         => $supplier->getKey(),
        ]);
        $order = Order::scoped(new ByUuid($data->get('id')))->first();
        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_PENDING_REQUESTED,
        ]);

        $this->assertDatabaseCount(ItemOrder::tableName(), 2);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'order_id'   => $order->getKey(),
            'created_at' => $order->created_at,
        ]);

        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'item_id'            => $item1->getKey(),
            'quantity'           => $quantity1,
            'quantity_requested' => $quantity1,
        ]);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'item_id'            => $item2->getKey(),
            'quantity'           => $quantity2,
            'quantity_requested' => $quantity2,
        ]);
    }

    /** @test */
    public function it_stores_an_order_and_the_item_orders_without_order_delivery_and_oem()
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $cart     = Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $item1    = Item::factory()->create();
        $item2    = Item::factory()->create();

        $quantity1 = 1;
        $quantity2 = 2;

        CartItem::factory()->usingCart($cart)->usingItem($item1)->create(['quantity' => $quantity1]);
        CartItem::factory()->usingCart($cart)->usingItem($item2)->create(['quantity' => $quantity2]);

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::OEM => null,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $data = Collection::make($response->json('data'));

        $this->assertDatabaseCount(Order::tableName(), 1);
        $this->assertDatabaseHas(Order::tableName(), [
            Order::routeKeyName() => $data->get('id'),
            'oem_id'              => null,
            'supplier_id'         => $supplier->getKey(),
        ]);

        $order = Order::scoped(new ByUuid($data->get('id')))->first();
        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_PENDING_REQUESTED,
        ]);

        $this->assertDatabaseCount(ItemOrder::tableName(), 2);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'item_id'  => $item1->getKey(),
            'quantity' => $quantity1,
        ]);
        $this->assertDatabaseHas(ItemOrder::tableName(), [
            'item_id'  => $item2->getKey(),
            'quantity' => $quantity2,
        ]);

        $this->assertDatabaseCount(OrderDelivery::tableName(), 0);
        $this->assertDatabaseMissing(OrderDelivery::tableName(), [
            'order_id' => $order->id,
        ]);
    }

    /** @test */
    public function it_creates_a_new_pubnub_channel_when_it_does_not_exits()
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $cart     = Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();
        CartItem::factory()->usingCart($cart)->create();

        $this->login($user);
        $route = URL::route($this->routeName);
        $this->post($route);

        $this->assertDatabaseCount('pubnub_channels', 1);
        $this->assertDatabaseHas('pubnub_channels', [
            'user_id'     => $user->getKey(),
            'supplier_id' => $supplier->getKey(),
        ]);
    }

    /** @test */
    public function it_return_the_same_pubnub_channel_if_exist_someone()
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $cart     = Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();
        CartItem::factory()->usingCart($cart)->create();
        $pubnubChannel = PubnubChannel::factory()->usingSupplier($supplier)->usingUser($user)->create();

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route);
        $data     = Collection::make($response->json('data'));

        $this->assertSame($data->get('channel'), $pubnubChannel->getRouteKey());
    }

    /** @test */
    public function it_dispatches_a_created_event_and_a_status_changed_event()
    {
        Event::fake([Created::class]);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $cart     = Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();
        CartItem::factory()->usingCart($cart)->create();

        $this->login($user);
        $route = URL::route($this->routeName);

        $this->post($route);

        Event::assertDispatched(Created::class);
    }

    /** @test */
    public function it_publishes_a_message_of_type_not_in_working_hours_for_the_user_in_the_pubnub_channel_when_the_supplier_is_not_in_working_hours(
    )
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $cart     = Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();
        CartItem::factory()->usingCart($cart)->create();
        $pubnubChannel = PubnubChannel::factory()->usingSupplier($supplier)->usingUser($user)->create();

        $supplierMessage = [
            'text' => 'New Quote Request',
            'type' => 'new_order',
        ];

        $userMessage = [
            'text' => 'Your Quote Request has been successfully sent. Currently this branch is closed, your supplier will see your request on their next business day.',
            'type' => 'text',
        ];

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->with($pubnubChannel->getRouteKey())->twice()->andReturnSelf();
        $pubnub->shouldReceive('message')->with($supplierMessage)->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with($userMessage)->once()->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->twice()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->twice()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        $this->login($user);
        $this->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_publishes_a_message_of_type_in_working_hours_for_the_user_in_the_pubnub_channel_when_the_supplier_is_in_working_hours(
    )
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $cart     = Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();
        CartItem::factory()->usingCart($cart)->create();

        $now           = Carbon::now();
        $supplierHour  = SupplierHour::factory()->createQuietly([
            'supplier_id' => $supplier,
            'day'         => strtolower($now->format('l')),
            'from'        => $now->clone()->startOfDay()->format('h:i a'),
            'to'          => $now->clone()->endOfDay()->format('h:i a'),
        ]);
        $pubnubChannel = PubnubChannel::factory()->usingSupplier($supplierHour->supplier)->usingUser($user)->create();

        $supplierMessage = [
            'text' => 'New Quote Request',
            'type' => 'new_order',
        ];

        $userMessage = [
            'text' => 'Your Quote Request has been successfully sent.',
            'type' => 'text',
        ];

        $pubnub = Mockery::mock(PubNub::class);
        $pubnub->shouldReceive('channel')->with($pubnubChannel->getRouteKey())->twice()->andReturnSelf();
        $pubnub->shouldReceive('message')->with($supplierMessage)->once()->andReturnSelf();
        $pubnub->shouldReceive('message')->with($userMessage)->once()->andReturnSelf();
        $pubnub->shouldReceive('publish')->withNoArgs()->twice()->andReturnSelf();
        $pubnub->shouldReceive('sync')->withNoArgs()->twice()->andReturnNull();
        App::bind(PubNub::class, fn() => $pubnub);

        $this->login($user);
        $this->post(URL::route($this->routeName));
    }

    /** @test */
    public function it_creates_a_relationship_supplier_user_when_it_does_not_exits()
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $cart     = Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();
        CartItem::factory()->usingCart($cart)->create();

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount(SupplierUser::tableName(), 1);
        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'user_id'         => $user->getKey(),
            'supplier_id'     => $supplier->getKey(),
            'visible_by_user' => false,
        ]);
    }

    /** @test */
    public function it_does_not_store_a_order_neither_cart_order_if_cart_not_exist()
    {
        Event::fake(Created::class);

        $user = User::factory()->create();
        $oem  = Oem::factory()->create();

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::OEM => $oem->getRouteKey(),
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseMissing(Order::tableName(), [
            'user_id' => $user->getKey(),
        ]);
        $this->assertDatabaseCount(CartOrder::tableName(), 0);
    }

    /** @test */
    public function it_does_not_store_an_order_neither_a_cart_order_if_cart_does_not_have_any_item()
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        Cart::factory()->usingUser($user)->usingSupplier($supplier)->create();

        $oem = Oem::factory()->create();

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::OEM => $oem->getRouteKey(),
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseMissing(Order::tableName(), [
            'user_id' => $user->getKey(),
        ]);
        $this->assertDatabaseCount(CartOrder::tableName(), 0);
    }
}
