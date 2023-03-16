<?php

namespace Tests\Feature\LiveApi\V2\Order;

use App;
use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Handlers\OrderSubstatus\OrderSubstatusCurriHandler;
use App\Handlers\OrderSubstatus\OrderSubstatusPickupHandler;
use App\Handlers\OrderSubstatus\OrderSubstatusShipmentHandler;
use App\Http\Controllers\LiveApi\V2\OrderController;
use App\Http\Requests\LiveApi\V2\Order\UpdateRequest;
use App\Http\Resources\LiveApi\V2\Order\DetailedResource;
use App\Models\CurriDelivery;
use App\Models\Media;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Pickup;
use App\Models\Setting;
use App\Models\ShipmentDelivery;
use App\Models\Staff;
use App\Models\Substatus;
use Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see OrderController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->patch(URL::route($this->routeName,
            [RouteParameters::ORDER => Order::factory()->createQuietly()->getRouteKey()]));
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
    public function it_updates_the_order_total_and_bid_number_and_note()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED)
            ->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->pickup()->create([
            'date'       => Carbon::now(),
            'start_time' => '09:00',
        ]);
        Pickup::factory()->usingOrderDelivery($orderDelivery)->create();
        Media::factory()->usingOrder($order)->create();

        Setting::factory()
            ->groupValidation()
            ->applicableToSupplier()
            ->create(['slug' => Setting::SLUG_BID_NUMBER_REQUIRED, 'value' => false]);

        Auth::shouldUse('live');
        $this->login($staff);

        $expectedBidNumber = '21589';
        $expectedTotal     = 67.89;
        $expectedNote      = 'Fake note';

        $response = $this->patch(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [
            RequestKeys::BID_NUMBER => $expectedBidNumber,
            RequestKeys::TOTAL      => $expectedTotal,
            RequestKeys::NOTE       => $expectedNote,
        ]);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema()), $response);
        $this->assertDatabaseHas(Order::tableName(), [
            'id'         => $order->getKey(),
            'bid_number' => $expectedBidNumber,
            'note'       => $expectedNote,
            'total'      => 6789,
        ]);
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_creates_and_execute_process_handler_depending_of_the_delivery_type(
        string $deliveryClass,
        string $deliveryType,
        string $handlerClass
    ) {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->create();

        Media::factory()->usingOrder($order)->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED)
            ->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create(['type' => $deliveryType]);
        /** @var CurriDelivery|Pickup|ShipmentDelivery $deliveryClass */
        $deliveryClass::factory()->usingOrderDelivery($orderDelivery)->create();

        Setting::factory()
            ->groupValidation()
            ->applicableToSupplier()
            ->create(['slug' => Setting::SLUG_BID_NUMBER_REQUIRED, 'value' => false]);

        $handler = Mockery::mock($handlerClass);
        $handler->shouldReceive('processPendingApprovalQuoteNeeded')->once()->withAnyArgs()->andReturn($order);
        App::bind($handlerClass, fn() => $handler);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->patch(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [
            RequestKeys::BID_NUMBER => 'foobar',
            RequestKeys::TOTAL      => 1000,
            RequestKeys::NOTE       => 'NOTE',
        ]);
        $response->assertStatus(Response::HTTP_OK);
    }

    public function dataProvider(): array
    {
        return [
            [CurriDelivery::class, OrderDelivery::TYPE_CURRI_DELIVERY, OrderSubstatusCurriHandler::class],
            [Pickup::class, OrderDelivery::TYPE_PICKUP, OrderSubstatusPickupHandler::class],
            [ShipmentDelivery::class, OrderDelivery::TYPE_SHIPMENT_DELIVERY, OrderSubstatusShipmentHandler::class],
        ];
    }

    /** @test
     * @dataProvider dataProviderSubstatuses
     */
    public function it_should_create_and_execute_process_handler_if_order_substatus_is_pending_approval_quote_needed(
        int $substatusId,
        bool $expected
    ) {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->create();

        Media::factory()->usingOrder($order)->create();

        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        $orderDelivery = OrderDelivery::factory()
            ->usingOrder($order)
            ->create(['type' => OrderDelivery::TYPE_CURRI_DELIVERY]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        Setting::factory()
            ->groupValidation()
            ->applicableToSupplier()
            ->create(['slug' => Setting::SLUG_BID_NUMBER_REQUIRED, 'value' => false]);

        if ($expected) {
            $handler = Mockery::mock(OrderSubstatusCurriHandler::class);
            $handler->shouldReceive('processPendingApprovalQuoteNeeded')->once()->withAnyArgs()->andReturn($order);
            App::bind(OrderSubstatusCurriHandler::class, fn() => $handler);
        }

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->patch(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [
            RequestKeys::BID_NUMBER => 'foobar',
            RequestKeys::TOTAL      => 1000,
            RequestKeys::NOTE       => 'NOTE',
        ]);
        $response->assertStatus(Response::HTTP_OK);
    }

    public function dataProviderSubstatuses(): array
    {
        return [
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, true],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, false],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, false],
            [Substatus::STATUS_APPROVED_DELIVERED, false],
        ];
    }
}
