<?php

namespace Tests\Feature\LiveApi\V1\Order\Complete;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\LegacyCompleted;
use App\Http\Controllers\LiveApi\V1\Order\CompleteController;
use App\Http\Resources\LiveApi\V1\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use Auth;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see CompleteController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_COMPLETE_STORE;

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
        $this->assertRouteUsesMiddleware($this->routeName, ['can:complete,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_sets_order_status_to_completed()
    {
        Event::fake(LegacyCompleted::class);

        $staff    = Staff::factory()->createQuietly(['name' => 'Example']);
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->approved()->create([
            'working_on_it' => 'John Doe',
        ]);
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]));

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(OrderSubstatus::tableName(),
            ['order_id' => $order->getKey(), 'substatus_id' => Substatus::STATUS_COMPLETED_DONE]);
    }

    /** @test */
    public function it_dispatches_a_completed_by_supplier_event_and_a_status_changed_event()
    {
        Event::fake([LegacyCompleted::class]);

        $staff    = Staff::factory()->createQuietly(['name' => 'Example']);
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->approved()->create([
            'working_on_it' => 'John Doe',
        ]);

        Auth::shouldUse('live');
        $this->login($staff);

        $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]));

        Event::assertDispatched(LegacyCompleted::class);
    }
}
