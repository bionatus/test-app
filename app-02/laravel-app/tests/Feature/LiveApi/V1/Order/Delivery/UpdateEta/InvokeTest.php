<?php

namespace Tests\Feature\LiveApi\V1\Order\Delivery\UpdateEta;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\DeliveryEtaUpdated;
use App\Http\Controllers\LiveApi\V1\Order\Delivery\UpdateEtaController;
use App\Http\Resources\LiveApi\V1\Order\Delivery\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see UpdateEtaController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_DELIVERY_ETA_UPDATE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $supplier = Supplier::factory()->createQuietly();
        $route    = URL::route($this->routeName, Order::factory()->usingSupplier($supplier)->create());

        $this->patch($route);
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:update,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_updates_an_eta_order_delivery()
    {
        Carbon::setTestNow('2022-10-11 05:00:00AM');

        $supplier      = Supplier::factory()->createQuietly();
        $staff         = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->pending()->usingSupplier($supplier)->create(['working_on_it' => 'John Doe']);
        $orderDelivery = OrderDelivery::factory()->pickup()->usingOrder($order)->create();
        Pickup::factory()->usingOrderDelivery($orderDelivery)->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $startTime = Carbon::createFromTime(6);
        $endTime   = Carbon::createFromTime(9);
        $response  = $this->patch(URL::route($this->routeName, $order), [
            RequestKeys::DATE       => $date = Carbon::now()->format('Y-m-d'),
            RequestKeys::START_TIME => $startTime->format('H:i'),
            RequestKeys::END_TIME   => $endTime->format('H:i'),
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $dbFormat = 'H:i:s';
        if (DB::connection()->getName() == 'sqlite') {
            $dbFormat = 'H:i';
        }

        $this->assertDatabaseHas(OrderDelivery::class, [
            'id'         => $orderDelivery->getKey(),
            'order_id'   => $order->getKey(),
            'date'       => $date,
            'start_time' => $startTime->format($dbFormat),
            'end_time'   => $endTime->format($dbFormat),
        ]);
    }

    /** @test */
    public function it_dispatches_a_order_delivery_eta_updated_event()
    {
        Event::fake([DeliveryEtaUpdated::class]);
        Carbon::setTestNow('2022-10-11 05:00:00AM');

        $supplier      = Supplier::factory()->createQuietly();
        $staff         = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->pending()->usingSupplier($supplier)->create(['working_on_it' => 'John Doe']);
        $orderDelivery = OrderDelivery::factory()->pickup()->usingOrder($order)->create();
        Pickup::factory()->usingOrderDelivery($orderDelivery)->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->patch(URL::route($this->routeName, $order), [
            RequestKeys::DATE       => Carbon::now()->format('Y-m-d'),
            RequestKeys::START_TIME => Carbon::createFromTime(6)->format('H:i'),
            RequestKeys::END_TIME   => Carbon::createFromTime(9)->format('H:i'),
        ]);

        $response->assertStatus(Response::HTTP_OK);
        Event::assertDispatched(DeliveryEtaUpdated::class);
    }
}
