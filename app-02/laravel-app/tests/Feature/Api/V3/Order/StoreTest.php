<?php

namespace Tests\Feature\Api\V3\Order;

use App;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Events\Order\Created;
use App\Http\Controllers\Api\V3\OrderController;
use App\Http\Requests\Api\V3\Order\StoreRequest;
use App\Http\Resources\Api\V3\Order\BaseResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartOrder;
use App\Models\CartOrderItem;
use App\Models\CurriDelivery;
use App\Models\Flag;
use App\Models\ForbiddenZipCode;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Oem;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\OtherDelivery;
use App\Models\Pickup;
use App\Models\PubnubChannel;
use App\Models\Scopes\ByUuid;
use App\Models\ShipmentDelivery;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\SupplierHour;
use App\Models\SupplierUser;
use App\Models\User;
use App\Models\WarehouseDelivery;
use DB;
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

    private string $routeName = RouteNames::API_V3_ORDER_STORE;

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
        $item     = Item::factory()->create();

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::SUPPLIER              => $supplier->getRouteKey(),
            RequestKeys::ITEMS                 => [
                [
                    "uuid"     => $item->getRouteKey(),
                    "quantity" => 2,
                ],
            ],
            RequestKeys::TYPE                  => 'warehouse_delivery',
            RequestKeys::REQUESTED_START_TIME  => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'Message to the supplier!',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
    }

    /** @test */
    public function it_stores_an_order_and_the_item_orders_and_order_delivery()
    {
        Event::fake(Created::class);

        $user      = User::factory()->create();
        $oem       = Oem::factory()->create();
        $supplier  = Supplier::factory()->createQuietly();
        $item1     = Item::factory()->create();
        $item2     = Item::factory()->create();
        $quantity1 = 1;
        $quantity2 = 2;

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::OEM                   => $oem->getRouteKey(),
            RequestKeys::SUPPLIER              => $supplier->getRouteKey(),
            RequestKeys::ITEMS                 => [
                [
                    "uuid"     => $item1->getRouteKey(),
                    "quantity" => $quantity1,
                ],
                [
                    "uuid"     => $item2->getRouteKey(),
                    "quantity" => $quantity2,
                ],
            ],
            RequestKeys::TYPE                  => 'warehouse_delivery',
            RequestKeys::REQUESTED_START_TIME  => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'Message to the supplier!',
        ]);

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

        $order = Order::where(Order::routeKeyName(), $data->get('id'))->first();
        $this->assertDatabaseCount(OrderDelivery::tableName(), 1);
        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'order_id' => $order->id,
            'type'     => 'warehouse_delivery',
        ]);
    }

    /** @test */
    public function it_stores_initially_item_orders_with_same_quantity_and_quantity_requested()
    {
        Event::fake(Created::class);

        $user      = User::factory()->create();
        $oem       = Oem::factory()->create();
        $supplier  = Supplier::factory()->createQuietly();
        $item1     = Item::factory()->create();
        $item2     = Item::factory()->create();
        $quantity1 = 1;
        $quantity2 = 2;

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::OEM                   => $oem->getRouteKey(),
            RequestKeys::SUPPLIER              => $supplier->getRouteKey(),
            RequestKeys::ITEMS                 => [
                [
                    "uuid"     => $item1->getRouteKey(),
                    "quantity" => $quantity1,
                ],
                [
                    "uuid"     => $item2->getRouteKey(),
                    "quantity" => $quantity2,
                ],
            ],
            RequestKeys::TYPE                  => 'warehouse_delivery',
            RequestKeys::REQUESTED_START_TIME  => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'Message to the supplier!',
        ]);

        $data = Collection::make($response->json('data'));

        $this->assertDatabaseCount(Order::tableName(), 1);
        $this->assertDatabaseHas(Order::tableName(), [
            Order::routeKeyName() => $data->get('id'),
            'oem_id'              => $oem->getKey(),
            'supplier_id'         => $supplier->getKey(),
        ]);

        $this->assertDatabaseCount(ItemOrder::tableName(), 2);
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
    public function it_stores_an_order_and_the_item_orders_and_order_delivery_without_oem()
    {
        Event::fake(Created::class);

        $user      = User::factory()->create();
        $supplier  = Supplier::factory()->createQuietly();
        $item1     = Item::factory()->create();
        $item2     = Item::factory()->create();
        $quantity1 = 1;
        $quantity2 = 2;

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::OEM                   => null,
            RequestKeys::SUPPLIER              => $supplier->getRouteKey(),
            RequestKeys::ITEMS                 => [
                [
                    "uuid"     => $item1->getRouteKey(),
                    "quantity" => $quantity1,
                ],
                [
                    "uuid"     => $item2->getRouteKey(),
                    "quantity" => $quantity2,
                ],
            ],
            RequestKeys::TYPE                  => 'warehouse_delivery',
            RequestKeys::REQUESTED_START_TIME  => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'Message to the supplier!',
        ]);
        $data     = Collection::make($response->json('data'));

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

        $this->assertDatabaseCount(OrderDelivery::tableName(), 1);
        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'order_id' => $order->id,
            'type'     => 'warehouse_delivery',
        ]);
    }

    /** @test */
    public function it_creates_a_new_pubnub_channel_when_it_does_not_exits()
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $item     = Item::factory()->create();

        $this->login($user);
        $route = URL::route($this->routeName);
        $this->post($route, [
            RequestKeys::SUPPLIER              => $supplier->getRouteKey(),
            RequestKeys::ITEMS                 => [
                [
                    "uuid"     => $item->getRouteKey(),
                    "quantity" => 7,
                ],
            ],
            RequestKeys::TYPE                  => 'warehouse_delivery',
            RequestKeys::REQUESTED_START_TIME  => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'Message to the supplier!',
        ]);

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

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $item          = Item::factory()->create();
        $pubnubChannel = PubnubChannel::factory()->usingSupplier($supplier)->usingUser($user)->create();

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::SUPPLIER              => $supplier->getRouteKey(),
            RequestKeys::ITEMS                 => [
                [
                    "uuid"     => $item->getRouteKey(),
                    "quantity" => 7,
                ],
            ],
            RequestKeys::TYPE                  => 'warehouse_delivery',
            RequestKeys::REQUESTED_START_TIME  => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'Message to the supplier!',
        ]);
        $data     = Collection::make($response->json('data'));

        $this->assertSame($data->get('channel'), $pubnubChannel->getRouteKey());
    }

    /** @test */
    public function it_dispatches_a_created_event_and_a_status_changed_event()
    {
        Event::fake([Created::class]);

        $user      = User::factory()->create();
        $supplier  = Supplier::factory()->createQuietly();
        $item1     = Item::factory()->create();
        $item2     = Item::factory()->create();
        $quantity1 = 1;
        $quantity2 = 2;

        $this->login($user);
        $route = URL::route($this->routeName);

        $this->post($route, [
            RequestKeys::SUPPLIER              => $supplier->getRouteKey(),
            RequestKeys::ITEMS                 => [
                [
                    "uuid"     => $item1->getRouteKey(),
                    "quantity" => $quantity1,
                ],
                [
                    "uuid"     => $item2->getRouteKey(),
                    "quantity" => $quantity2,
                ],
            ],
            RequestKeys::TYPE                  => 'warehouse_delivery',
            RequestKeys::REQUESTED_START_TIME  => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'Message to the supplier!',
        ]);

        Event::assertDispatched(Created::class);
    }

    /** @test */
    public function it_publishes_a_message_of_type_not_in_working_hours_for_the_user_in_the_pubnub_channel_when_the_supplier_is_not_in_working_hours(
    )
    {
        Event::fake(Created::class);

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $item          = Item::factory()->create();
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
        $this->post(URL::route($this->routeName), [
            RequestKeys::SUPPLIER              => $supplier->getRouteKey(),
            RequestKeys::ITEMS                 => [
                [
                    "uuid"     => $item->getRouteKey(),
                    "quantity" => 7,
                ],
            ],
            RequestKeys::TYPE                  => 'warehouse_delivery',
            RequestKeys::REQUESTED_START_TIME  => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'Message to the supplier!',
        ]);
    }

    /** @test */
    public function it_publishes_a_message_of_type_in_working_hours_for_the_user_in_the_pubnub_channel_when_the_supplier_is_in_working_hours(
    )
    {
        Event::fake(Created::class);

        $user          = User::factory()->create();
        $now           = Carbon::now();
        $supplierHour  = SupplierHour::factory()->createQuietly([
            'supplier_id' => Supplier::factory(),
            'day'         => strtolower($now->format('l')),
            'from'        => $now->clone()->startOfDay()->format('h:i a'),
            'to'          => $now->clone()->endOfDay()->format('h:i a'),
        ]);
        $item          = Item::factory()->create();
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
        $this->post(URL::route($this->routeName), [
            RequestKeys::SUPPLIER              => $supplierHour->supplier->getRouteKey(),
            RequestKeys::ITEMS                 => [
                [
                    "uuid"     => $item->getRouteKey(),
                    "quantity" => 7,
                ],
            ],
            RequestKeys::TYPE                  => 'warehouse_delivery',
            RequestKeys::REQUESTED_START_TIME  => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'Message to the supplier!',
        ]);
    }

    /** @test */
    public function it_creates_a_relationship_supplier_user_when_it_does_not_exits()
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $item     = Item::factory()->create();

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::SUPPLIER             => $supplier->getRouteKey(),
            RequestKeys::ITEMS                => [
                [
                    "uuid"     => $item->getRouteKey(),
                    "quantity" => 7,
                ],
            ],
            RequestKeys::TYPE                 => 'pickup',
            RequestKeys::REQUESTED_START_TIME => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME   => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::REQUESTED_DATE       => '2022-12-20',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount(SupplierUser::tableName(), 1);
        $this->assertDatabaseHas(SupplierUser::tableName(), [
            'user_id'         => $user->getKey(),
            'supplier_id'     => $supplier->getKey(),
            'visible_by_user' => false,
        ]);
    }

    /** @test
     * @dataProvider typeDeliveryProvider
     */
    public function it_creates_a_correct_order_delivery(string $typeDelivery, string $tableName)
    {
        Event::fake(Created::class);

        $user      = User::factory()->create();
        $supplier  = Supplier::factory()->createQuietly(['zip_code' => '12345']);
        $item      = Item::factory()->create();
        $date      = '2022-12-20';
        $startTime = Carbon::createFromTime(9);
        $endTime   = Carbon::createFromTime(12);

        $params = [
            RequestKeys::SUPPLIER             => $supplier->getRouteKey(),
            RequestKeys::ITEMS                => [
                [
                    "uuid"     => $item->getRouteKey(),
                    "quantity" => 7,
                ],
            ],
            RequestKeys::TYPE                 => $typeDelivery,
            RequestKeys::REQUESTED_DATE       => $date,
            RequestKeys::REQUESTED_START_TIME => $startTime->format('H:i'),
            RequestKeys::REQUESTED_END_TIME   => $endTime->format('H:i'),
        ];

        if ($typeDelivery != 'pickup') {
            $params = array_merge($params, [
                RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
                RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
                RequestKeys::DESTINATION_COUNTRY   => 'US',
                RequestKeys::DESTINATION_STATE     => 'New York',
                RequestKeys::DESTINATION_CITY      => 'New York',
                RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            ]);
        }

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, $params);

        $response->assertStatus(Response::HTTP_CREATED);
        $data = Collection::make($response->json('data'));

        $timeFormat = 'H:i:s';
        if ('sqlite' === DB::connection()->getName()) {
            $timeFormat = 'H:i';
        }

        $order = Order::scoped(new ByUuid($data->get('id')))->first();
        $this->assertDatabaseCount(OrderDelivery::tableName(), 1);
        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'order_id'             => $order->getKey(),
            'type'                 => $typeDelivery,
            'requested_date'       => $date,
            'requested_start_time' => $startTime->format($timeFormat),
            'requested_end_time'   => $endTime->format($timeFormat),
            'date'                 => $date,
            'start_time'           => $startTime->format($timeFormat),
            'end_time'             => $endTime->format($timeFormat),
        ]);

        $orderDelivery = OrderDelivery::where('order_id', $order->getKey())->first();
        $this->assertDatabaseHas($tableName, [
            'id' => $orderDelivery->getKey(),
        ]);
    }

    public function typeDeliveryProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, CurriDelivery::tableName()],
            [OrderDelivery::TYPE_OTHER_DELIVERY, OtherDelivery::tableName()],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, ShipmentDelivery::tableName()],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, WarehouseDelivery::tableName()],
            [OrderDelivery::TYPE_PICKUP, Pickup::tableName()],
        ];
    }

    /** @test */
    public function it_stores_a_cart_order_with_the_items()
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $oem      = Oem::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $item1    = Item::factory()->create();
        $item2    = Item::factory()->create();

        $cart        = Cart::factory()->usingUser($user)->create();
        $cartItemOne = CartItem::factory()->usingCart($cart)->usingItem($item1)->create();
        $cartItemTwo = CartItem::factory()->usingCart($cart)->usingItem($item2)->create();

        $quantity1 = $cartItemOne->quantity;
        $quantity2 = $cartItemTwo->quantity;

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::OEM                   => $oem->getRouteKey(),
            RequestKeys::SUPPLIER              => $supplier->getRouteKey(),
            RequestKeys::ITEMS                 => [
                [
                    "uuid"     => $item1->getRouteKey(),
                    "quantity" => $quantity1,
                ],
                [
                    "uuid"     => $item2->getRouteKey(),
                    "quantity" => $quantity2,
                ],
            ],
            RequestKeys::TYPE                  => 'warehouse_delivery',
            RequestKeys::REQUESTED_START_TIME  => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'Message to the supplier!',
        ]);

        $data = Collection::make($response->json('data'));

        $order = Order::where(Order::routeKeyName(), $data->get('id'))->first();

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas(CartOrder::tableName(), ['order_id' => $order->getKey()]);
        $this->assertDatabaseCount(CartOrderItem::tableName(), 2);
        $this->assertDatabaseHas(CartOrderItem::tableName(), [
            'item_id'  => $item1->getKey(),
            'quantity' => $quantity1,
        ]);
        $this->assertDatabaseHas(CartOrderItem::tableName(), [
            'item_id'  => $item2->getKey(),
            'quantity' => $quantity2,
        ]);
    }

    /** @test */
    public function it_does_not_store_a_cart_order_if_cart_not_exist()
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $oem      = Oem::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $item1    = Item::factory()->create();
        $item2    = Item::factory()->create();

        $quantity1 = 1;
        $quantity2 = 2;

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::OEM                   => $oem->getRouteKey(),
            RequestKeys::SUPPLIER              => $supplier->getRouteKey(),
            RequestKeys::ITEMS                 => [
                [
                    "uuid"     => $item1->getRouteKey(),
                    "quantity" => $quantity1,
                ],
                [
                    "uuid"     => $item2->getRouteKey(),
                    "quantity" => $quantity2,
                ],
            ],
            RequestKeys::TYPE                  => 'warehouse_delivery',
            RequestKeys::REQUESTED_START_TIME  => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'Message to the supplier!',
        ]);

        $data = Collection::make($response->json('data'));

        $order = Order::where(Order::routeKeyName(), $data->get('id'))->first();

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertNull($order->cartOrder);
    }

    /** @test */
    public function it_does_not_store_a_cart_order_if_cart_does_not_have_any_item()
    {
        Event::fake(Created::class);

        $user     = User::factory()->create();
        $oem      = Oem::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $item1    = Item::factory()->create();
        $item2    = Item::factory()->create();

        Cart::factory()->usingUser($user)->create();

        $quantity1 = 1;
        $quantity2 = 2;

        $this->login($user);
        $route    = URL::route($this->routeName);
        $response = $this->post($route, [
            RequestKeys::OEM                   => $oem->getRouteKey(),
            RequestKeys::SUPPLIER              => $supplier->getRouteKey(),
            RequestKeys::ITEMS                 => [
                [
                    "uuid"     => $item1->getRouteKey(),
                    "quantity" => $quantity1,
                ],
                [
                    "uuid"     => $item2->getRouteKey(),
                    "quantity" => $quantity2,
                ],
            ],
            RequestKeys::TYPE                  => 'warehouse_delivery',
            RequestKeys::REQUESTED_START_TIME  => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => Carbon::createFromTime(12)->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'Message to the supplier!',
        ]);

        $data = Collection::make($response->json('data'));

        $order = Order::where(Order::routeKeyName(), $data->get('id'))->first();

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertNull($order->cartOrder);
    }

    /**
     * @test
     * @dataProvider infoProvider
     */
    public function its_supplier_zip_code_and_flag_should_be_not_forbidden_if_delivery_type_is_curri(
        string $forbiddenZipCode,
        ?string $supplierZipCode,
        bool $flagForbiddenCurri,
        string $deliveryTypeResult
    ) {
        Event::fake(Created::class);

        $invalidZipcode = $forbiddenZipCode;
        ForbiddenZipCode::factory()->create(['zip_code' => $invalidZipcode]);
        $user      = User::factory()->create();
        $oem       = Oem::factory()->create();
        $supplier  = Supplier::factory()->createQuietly(['zip_code' => $supplierZipCode]);
        $item1     = Item::factory()->create();
        $item2     = Item::factory()->create();
        $startTime = Carbon::createFromTime(9);
        $endTime   = Carbon::createFromTime(12);

        if ($flagForbiddenCurri) {
            Flag::factory()->usingModel($supplier)->create(['name' => Flag::FORBIDDEN_CURRI]);
        }

        $this->login($user);
        $route = URL::route($this->routeName);

        $params = [
            RequestKeys::OEM                   => $oem->getRouteKey(),
            RequestKeys::SUPPLIER              => $supplier->getRouteKey(),
            RequestKeys::ITEMS                 => [
                [
                    "uuid"     => $item1->getRouteKey(),
                    "quantity" => 1,
                ],
                [
                    "uuid"     => $item2->getRouteKey(),
                    "quantity" => 2,
                ],
            ],
            RequestKeys::TYPE                  => OrderDelivery::TYPE_CURRI_DELIVERY,
            RequestKeys::REQUESTED_START_TIME  => $startTime->format('H:i'),
            RequestKeys::REQUESTED_END_TIME    => $endTime->format('H:i'),
            RequestKeys::REQUESTED_DATE        => '2022-12-20',
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'New York',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '456132',
            RequestKeys::NOTE                  => 'Message to the supplier!',
        ];

        $response = $this->post($route, $params);

        $data = Collection::make($response->json('data'));

        $timeFormat = 'H:i:s';
        if ('sqlite' === DB::connection()->getName()) {
            $timeFormat = 'H:i';
        }

        $order = Order::scoped(new ByUuid($data->get('id')))->first();

        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'order_id'             => $order->getKey(),
            'type'                 => $deliveryTypeResult,
            'requested_date'       => '2022-12-20',
            'requested_start_time' => $startTime->format($timeFormat),
            'requested_end_time'   => $endTime->format($timeFormat),
            'date'                 => '2022-12-20',
            'start_time'           => $startTime->format($timeFormat),
            'end_time'             => $endTime->format($timeFormat),
        ]);
    }

    public function infoProvider(): array
    {
        return [
            ['2222', '1111', false, OrderDelivery::TYPE_CURRI_DELIVERY], //can use
            ['1111', '1111', false, OrderDelivery::TYPE_WAREHOUSE_DELIVERY], //can't use because forbiddenZipCode
            ['2222', '1111', true, OrderDelivery::TYPE_WAREHOUSE_DELIVERY], //can't use because flag forbiddenCurri
            ['1111', '1111', true, OrderDelivery::TYPE_WAREHOUSE_DELIVERY], //can't use because both
            ['3333', null, false, OrderDelivery::TYPE_WAREHOUSE_DELIVERY],
            ['3333', null, true, OrderDelivery::TYPE_WAREHOUSE_DELIVERY],
        ];
    }
}
