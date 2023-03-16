<?php

namespace Tests\Feature\Api\V4\Order\Delivery;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V4\Order\DeliveryController;
use App\Http\Requests\Api\V4\Order\Delivery\StoreRequest;
use App\Http\Resources\Models\OrderDeliveryResource;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use App\Models\ShipmentDelivery;
use App\Models\ShipmentDeliveryPreference;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\SupplierHour;
use App\Models\User;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see DeliveryController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V4_ORDER_DELIVERY_STORE;
    private string $route;
    private User   $user;
    private Order  $order;

    public function setUp(): void
    {
        parent::setUp();
        $this->user  = User::factory()->create();
        $supplier    = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $this->order = Order::factory()->usingUser($this->user)->usingSupplier($supplier)->pending()->create();
        $date        = Carbon::createFromFormat('Y-m-d', '2023-02-10');
        SupplierHour::factory()->usingSupplier($supplier)->create([
            'day'  => strtolower($date->format('l')),
            'from' => $date->clone()->startOfDay()->format('h:i a'),
            'to'   => $date->clone()->endOfDay()->format('h:i a'),
        ]);
        $this->order->substatuses()->withTimestamps()->attach(Substatus::STATUS_PENDING_APPROVAL_FULFILLED);

        $this->route = URL::route($this->routeName, [RouteParameters::ORDER => $this->order]);
    }

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post($this->route);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_returns_the_correct_base_resource_schema()
    {
        $date = '2023-02-10';
        Carbon::setTestNow($date . ' 09:29:00');
        $this->login($this->user);
        $response = $this->post($this->route, [
            RequestKeys::TYPE                 => OrderDelivery::TYPE_CURRI_DELIVERY,
            RequestKeys::REQUESTED_START_TIME => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME   => Carbon::createFromTime(10)->format('H:i'),
            RequestKeys::REQUESTED_DATE       => $date,
            RequestKeys::IS_NEEDED_NOW        => false,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(OrderDeliveryResource::jsonSchema()), $response);
    }

    /** @test */
    public function it_stores_an_order_delivery()
    {
        $date = '2023-02-10';
        Carbon::setTestNow($date . ' 09:29:00');
        $startTime = Carbon::createFromTime(9);
        $endTime   = Carbon::createFromTime(10);

        $this->login($this->user);
        $this->post($this->route, [
            RequestKeys::TYPE                 => $type = OrderDelivery::TYPE_CURRI_DELIVERY,
            RequestKeys::REQUESTED_START_TIME => $startTime->format('H:i'),
            RequestKeys::REQUESTED_END_TIME   => $endTime->format('H:i'),
            RequestKeys::REQUESTED_DATE       => $date,
            RequestKeys::IS_NEEDED_NOW        => false,
        ]);

        $timeFormat = 'H:i:s';
        if ('sqlite' === DB::connection()->getName()) {
            $timeFormat = 'H:i';
        }
        $this->assertDatabaseCount(OrderDelivery::tableName(), 1);

        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'order_id'             => $this->order->getKey(),
            'type'                 => $type,
            'requested_date'       => $date,
            'requested_start_time' => $startTime->format($timeFormat),
            'requested_end_time'   => $endTime->format($timeFormat),
            'date'                 => $date,
            'start_time'           => $startTime->format($timeFormat),
            'end_time'             => $endTime->format($timeFormat),
            'is_needed_now'        => false,
            'note'                 => null,
        ]);
    }

    /** @test
     * @dataProvider typeDeliveryProvider
     */
    public function it_store_a_correct_order_delivery($typeDelivery, $tableName, $isShipment)
    {
        $date = '2023-02-10';
        Carbon::setTestNow($date . ' 09:29:00');
        ShipmentDeliveryPreference::factory()->create(['slug' => 'overnight']);

        $startTime = Carbon::createFromTime(9);
        $endTime   = Carbon::createFromTime(10);

        $params = [
            RequestKeys::TYPE                 => $typeDelivery,
            RequestKeys::REQUESTED_DATE       => $date,
            RequestKeys::REQUESTED_START_TIME => $startTime->format('H:i'),
            RequestKeys::REQUESTED_END_TIME   => $endTime->format('H:i'),
            RequestKeys::IS_NEEDED_NOW        => false,
        ];
        if ($isShipment) {
            $params = [
                RequestKeys::TYPE                => $typeDelivery,
                RequestKeys::SHIPMENT_PREFERENCE => 'overnight',
                RequestKeys::IS_NEEDED_NOW       => false,
            ];
        }

        $this->login($this->user);

        $response = $this->post($this->route, $params);

        $response->assertStatus(Response::HTTP_CREATED);

        $timeFormat = 'H:i:s';
        if ('sqlite' === DB::connection()->getName()) {
            $timeFormat = 'H:i';
        }
        $fields = [
            'order_id'             => $this->order->getKey(),
            'type'                 => $typeDelivery,
            'requested_date'       => $date,
            'requested_start_time' => $startTime->format($timeFormat),
            'requested_end_time'   => $endTime->format($timeFormat),
            'date'                 => $date,
            'start_time'           => $startTime->format($timeFormat),
            'end_time'             => $endTime->format($timeFormat),
            'is_needed_now'        => false,
        ];
        if ($isShipment) {
            $fields = [
                'order_id' => $this->order->getKey(),
                'type'     => $typeDelivery,
            ];
        }

        $this->assertDatabaseCount(OrderDelivery::tableName(), 1);
        $this->assertDatabaseHas(OrderDelivery::tableName(), $fields);

        $this->assertDatabaseHas($tableName, [
            'id' => $this->order->orderDelivery->getKey(),
        ]);
    }

    public function typeDeliveryProvider(): array
    {
        return [
            //new order delivery type, delivery type table name, old delivery type, old class name delivery type
            [
                OrderDelivery::TYPE_CURRI_DELIVERY,
                CurriDelivery::tableName(),
                false,
            ],
            [
                OrderDelivery::TYPE_PICKUP,
                Pickup::tableName(),
                false,
            ],
            [
                OrderDelivery::TYPE_SHIPMENT_DELIVERY,
                ShipmentDelivery::tableName(),
                true,
            ],
        ];
    }
}
