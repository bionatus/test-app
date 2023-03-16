<?php

namespace Tests\Feature\LiveApi\V1\Order\Reopen;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\Reopen;
use App\Http\Resources\LiveApi\V1\Order\Unprocessed\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use Auth;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ReopenController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_REOPEN_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName,
            [RouteParameters::ORDER => Order::factory()->createQuietly()->getRouteKey()]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:reopen,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_puts_an_order_in_pending_status()
    {
        Event::fake(Reopen::class);

        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create([
            'working_on_it' => 'John Doe',
            'bid_number'    => '21589',
        ]);
        OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => Carbon::now(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $this->assertDatabaseHas(OrderSubstatus::tableName(), [
            'order_id'     => $order->getKey(),
            'substatus_id' => Substatus::STATUS_PENDING_REQUESTED,
        ]);

        $this->assertSame($order->lastStatus->substatus_id, Substatus::STATUS_PENDING_REQUESTED);
    }

    /** @test */
    public function it_dispatches_a_reopen_event()
    {
        Event::fake(Reopen::class);

        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create([
            'working_on_it' => 'John Doe',
            'bid_number'    => '21589',
        ]);
        OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => Carbon::now(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));

        $response->assertStatus(Response::HTTP_CREATED);
        Event::assertDispatched(Reopen::class);
    }
}
