<?php

namespace Tests\Feature\LiveApi\V1\Order\PreApproval;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Order\PreApprovalController;
use App\Http\Requests\LiveApi\V1\Order\PreApproval\InvokeRequest;
use App\Http\Resources\LiveApi\V1\Order\Unprocessed\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Setting;
use App\Models\Staff;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see PreApprovalController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_PRE_APPROVAL_STORE;

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
        $this->assertRouteUsesMiddleware($this->routeName, ['can:preApprove,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_saves_bid_number()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        Setting::factory()
            ->groupValidation()
            ->applicableToSupplier()
            ->create(['slug' => Setting::SLUG_BID_NUMBER_REQUIRED, 'value' => false]);
        OrderDelivery::factory()->usingOrder($order)->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $expectedBidNumber = '21589';

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [
            RequestKeys::BID_NUMBER => $expectedBidNumber,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertDatabaseHas(Order::tableName(), [
            'id'         => $order->getKey(),
            'bid_number' => $expectedBidNumber,
        ]);
    }
}
