<?php

namespace Tests\Feature\LiveApi\V1\Order\InProgress\Delivery\Curri\Notice\EnRoute;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\Delivery\Curri\Notice\EnRoute\ConfirmedBySupplier;
use App\Http\Controllers\LiveApi\V1\Order\InProgress\Delivery\Curri\Notice\EnRoute\ConfirmController;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ConfirmController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_DELIVERY_CURRI_NOTICE_ENROUTE_CONFIRM_STORE;

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
        $this->assertRouteUsesMiddleware($this->routeName,
            ['can:confirmNoticeEnRouteCurriDelivery,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_confirms_a_notice_en_route_of_a_curri_delivery_order()
    {
        Event::fake(ConfirmedBySupplier::class);

        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->completed()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create(['book_id' => 'abc_123']);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function it_dispatches_a_curri_notification_en_route_confirmed_event()
    {
        Event::fake(ConfirmedBySupplier::class);

        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->completed()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create(['book_id' => 'abc_123']);

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_OK);

        Event::assertDispatched(ConfirmedBySupplier::class);
    }
}
