<?php

namespace Tests\Feature\LiveApi\V2\Order;

use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Resources\LiveApi\V2\Order\DetailedResource;
use App\Models\Order;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see OrderController */
class ShowTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = LiveApiV2::LIVE_API_V2_ORDER_SHOW;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName, [RouteParameters::ORDER => $order]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:read,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_displays_an_order()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->usingSupplier($staff->supplier)->pendingApproval()->create();

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order]);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->get($route);
        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(DetailedResource::jsonSchema(), false), $response);

        $data = Collection::make($response->json('data'));
        $this->assertEquals($order->getRouteKey(), $data['id']);
    }
}
