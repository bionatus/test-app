<?php

namespace Tests\Unit\Http\Requests\Api\V3\Order\Cancel;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V3\Order\CancelController;
use App\Http\Requests\Api\V3\Order\Cancel\StoreRequest;
use App\Models\Order;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Route;
use Str;
use Tests\Unit\Http\Requests\RequestTestCase;
use URL;

/** @see CancelController */
class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    private string   $route;
    private Order    $order;
    protected string $requestClass = StoreRequest::class;

    protected function setUp(): void
    {
        parent::setUp();

        $supplier    = Supplier::factory()->createQuietly();
        $this->order = Order::factory()->pendingApproval()->usingSupplier($supplier)->create();

        $this->route = URL::route(RouteNames::API_V3_ORDER_CANCEL_STORE, ['order' => $this->order]);

        Route::model(RouteParameters::ORDER, Order::class);
    }

    /** @test */
    public function it_should_authorize()
    {
        $this->formRequest($this->requestClass)
            ->addRouteParameter(RouteParameters::ORDER, $this->order->getRouteKey())
            ->assertAuthorized();
    }

    /** @test */
    public function its_status_detail_parameter_is_required_when_status_is_pending_approval()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATUS_DETAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATUS_DETAIL);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_status_detail_parameter_must_be_a_string_when_status_is_pending_approval()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATUS_DETAIL => 1],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATUS_DETAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATUS_DETAIL);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $attribute]),
        ]);
    }

    /** @test */
    public function its_status_detail_must_be_at_most_255_characters_long_when_status_is_pending_approval()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STATUS_DETAIL => Str::random(256)],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STATUS_DETAIL]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STATUS_DETAIL);
        $request->assertValidationMessages([
            Lang::get('validation.max.string', ['attribute' => $attribute, 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_pass_on_valid_data_for_pending_order()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();
        $route    = URL::route(RouteNames::API_V3_ORDER_CANCEL_STORE, ['order' => $order]);

        $data    = [];
        $request = $this->formRequest($this->requestClass, $data, ['method' => 'post', 'route' => $route]);

        $request->assertValidationPassed();
    }

    /** @test */
    public function it_pass_on_valid_data_for_pending_approval_order()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();
        $route    = URL::route(RouteNames::API_V3_ORDER_CANCEL_STORE,
            ['order' => $order, 'status_detail' => 'Took too long']);

        $data    = [];
        $request = $this->formRequest($this->requestClass, $data, ['method' => 'post', 'route' => $route]);

        $request->assertValidationPassed();
    }
}
