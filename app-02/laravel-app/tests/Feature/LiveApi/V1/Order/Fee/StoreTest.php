<?php

namespace Tests\Feature\LiveApi\V1\Order\Fee;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Requests\LiveApi\V1\Order\Fee\StoreRequest;
use App\Http\Resources\LiveApi\V1\Order\BaseResource;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Staff;
use App\Models\Supplier;
use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see FeeController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_FEE_STORE;

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
        $this->assertRouteUsesMiddleware($this->routeName, ['can:update,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test
     *
     * @dataProvider provider
     *
     * @param string|null $discount
     * @param string|null $tax
     * @param float       $expectedDiscount
     * @param float       $expectedTax
     */
    public function it_sets_discount_and_tax_fields(
        ?string $discount,
        ?string $tax,
        float $expectedDiscount,
        float $expectedTax
    ) {
        $staff    = Staff::factory()->createQuietly();
        $supplier = $staff->supplier;
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        OrderDelivery::factory()->usingOrder($order)->create();

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->post(URL::route($this->routeName, [
            RouteParameters::ORDER => $order->getRouteKey(),
        ]), [
            RequestKeys::DISCOUNT => $discount,
            RequestKeys::TAX      => $tax,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), false), $response);
        $this->assertDatabaseHas(Order::tableName(), [
            'id'       => $order->getKey(),
            'discount' => $expectedDiscount,
            'tax'      => $expectedTax,
        ]);
    }

    public function provider()
    {
        return [
            ['12.34', '56.78', 1234, 5678],
            ['12', null, 1200, 0],
            [null, '56.7', 0, 5670],
            [null, null, 0, 0],
        ];
    }
}
