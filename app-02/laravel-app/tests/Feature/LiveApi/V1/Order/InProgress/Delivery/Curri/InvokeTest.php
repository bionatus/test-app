<?php

namespace Tests\Feature\LiveApi\V1\Order\InProgress\Delivery\Curri;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Order\InProgress\Delivery\Curri\ConfirmController;
use App\Http\Resources\LiveApi\V1\Order\BaseResource;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Carbon\Carbon;
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

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_DELIVERY_CURRI_CONFIRM_STORE;

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
        $this->assertRouteUsesMiddleware($this->routeName, ['can:confirmCurriOrder,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_confirms_a_curri_delivery_order()
    {
        $supplier      = Supplier::factory()->withEmail()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => Carbon::now()->addDay(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(17)->format('H:i'),
        ]);
        $curriDelivery = CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        Auth::shouldUse('live');
        $this->login(Staff::factory()->usingSupplier($supplier)->create());

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertNotNull($curriDelivery->refresh()->supplier_confirmed_at);
    }
}
