<?php

namespace Tests\Feature\LiveApi\V2\Order\SendForApproval;

use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V2\Order\SendForApprovalController;
use App\Http\Requests\LiveApi\V2\Order\SendForApproval\InvokeRequest;
use App\Http\Resources\LiveApi\V2\Order\DetailedResource;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\Substatus;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see SendForApprovalController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_SEND_FOR_APPROVAL_STORE;

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
        $this->assertRouteUsesMiddleware($this->routeName, ['can:sendForApproval,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_updates_the_order_total_and_bid_number_and_note()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId(Substatus::STATUS_PENDING_ASSIGNED)->create();
        Setting::factory()
            ->groupValidation()
            ->applicableToSupplier()
            ->create(['slug' => Setting::SLUG_BID_NUMBER_REQUIRED, 'value' => false]);

        Auth::shouldUse('live');
        $this->login($staff);

        $expectedBidNumber = '21589';
        $expectedTotal     = 67.89;
        $expectedNote      = 'Fake note';

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [
            RequestKeys::BID_NUMBER => $expectedBidNumber,
            RequestKeys::TOTAL      => $expectedTotal,
            RequestKeys::NOTE       => $expectedNote,
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema()), $response);
        $this->assertDatabaseHas(Order::tableName(), [
            'id'         => $order->getKey(),
            'bid_number' => $expectedBidNumber,
            'note'       => $expectedNote,
            'total'      => 6789,
        ]);
    }

    /** @test */
    public function it_updates_the_status_of_the_order_to_pending_approval_fulfilled()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId(Substatus::STATUS_PENDING_ASSIGNED)->create();
        Setting::factory()
            ->groupValidation()
            ->applicableToSupplier()
            ->create(['slug' => Setting::SLUG_BID_NUMBER_REQUIRED, 'value' => false]);
        
        Auth::shouldUse('live');
        $this->login($staff);

        $expectedBidNumber = '21589';
        $expectedTotal     = 67.89;
        $expectedNote      = 'Fake note';

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [
            RequestKeys::BID_NUMBER => $expectedBidNumber,
            RequestKeys::TOTAL      => $expectedTotal,
            RequestKeys::NOTE       => $expectedNote,
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema()), $response);
        $this->assertDatabaseHas(Order::tableName(), [
            'id'         => $order->getKey(),
            'bid_number' => $expectedBidNumber,
            'note'       => $expectedNote,
            'total'      => 6789,
        ]);

        $this->assertEquals(Substatus::STATUS_PENDING_APPROVAL_FULFILLED, $order->lastStatus->substatus_id);
    }
}
