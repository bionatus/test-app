<?php

namespace Tests\Feature\LiveApi\V1\Order\Assign;

use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V1\Order\AssignController;
use App\Http\Resources\LiveApi\V1\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see AssignController */
class CancelTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_ASSIGNMENT_DELETE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);
        $supplier = Supplier::factory()->createQuietly();
        $this->delete(URL::route($this->routeName,
            [RouteParameters::ORDER => Order::factory()->usingSupplier($supplier)->create()->getRouteKey()]));
    }

    /** @test */
    public function it_sets_null_working_on_it_field_successfully()
    {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();
        Order::query()->each(function(Order $order) {
            OrderDelivery::factory()->usingOrder($order)->create();
        });
        $workingOnIt = null;

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->delete(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]));

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $data = $response->json('data');

        $this->assertNull($data['working_on_it']);
        $this->assertDatabaseHas(Order::tableName(), ['id' => $order->getKey(), 'working_on_it' => $workingOnIt]);
    }
}
