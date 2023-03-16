<?php

namespace Tests\Unit\Http\Requests\LiveApi\V2\Order\Assignment;

use App\Constants\RequestKeys;
use App\Constants\RouteNames\LiveApiV2;
use App\Constants\RouteParameters;
use App\Http\Controllers\LiveApi\V2\Order\AssignController;
use App\Http\Requests\LiveApi\V2\Order\Assignment\StoreRequest;
use App\Models\Order;
use App\Models\Staff;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;
use URL;

/** @see AssignController */
class StoreRequestTest extends RequestTestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected string $requestClass = StoreRequest::class;
    private string   $route;

    protected function setUp(): void
    {
        parent::setUp();

        $supplier    = Supplier::factory()->createQuietly();
        $order       = Order::factory()->usingSupplier($supplier)->create();
        $this->route = URL::route(LiveApiV2::LIVE_API_V2_ORDER_ASSIGNMENT_STORE, [RouteParameters::ORDER => $order]);
    }

    /** @test */
    public function its_staff_parameter_must_be_required()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STAFF]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::STAFF);
        $request->assertValidationMessages([Lang::get('validation.required', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_staff_parameter_must_exist_in_database()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::STAFF => 'invalid'],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STAFF]);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => RequestKeys::STAFF])]);
    }

    /** @test */
    public function its_staff_parameter_should_be_a_counter_staff()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->create();

        $request = $this->formRequest($this->requestClass, [RequestKeys::STAFF => $staff->getRouteKey()],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STAFF]);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => RequestKeys::STAFF])]);
    }

    /** @test */
    public function its_staff_parameter_should_belong_to_the_supplier_of_the_order()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->create();

        $anotherSupplier = Supplier::factory()->createQuietly();
        $order           = Order::factory()->usingSupplier($anotherSupplier)->create();

        $route   = URL::route(LiveApiV2::LIVE_API_V2_ORDER_ASSIGNMENT_STORE, [RouteParameters::ORDER => $order]);
        $request = $this->formRequest($this->requestClass, [RequestKeys::STAFF => $staff->getRouteKey()],
            ['method' => 'post', 'route' => $route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::STAFF]);
        $request->assertValidationMessages([Lang::get('validation.exists', ['attribute' => RequestKeys::STAFF])]);
    }

    /** @test */
    public function it_pass_on_valid_data()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        $route    = URL::route(LiveApiV2::LIVE_API_V2_ORDER_ASSIGNMENT_STORE, [RouteParameters::ORDER => $order]);

        $request = $this->formRequest($this->requestClass, [RequestKeys::STAFF => $staff->getRouteKey()],
            ['method' => 'post', 'route' => $route]);

        $request->assertValidationPassed();
    }

    /** @test */
    public function it_should_authorize()
    {
        $supplier = Supplier::factory()->createQuietly();
        $staff    = Staff::factory()->usingSupplier($supplier)->counter()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        $route    = URL::route(LiveApiV2::LIVE_API_V2_ORDER_ASSIGNMENT_STORE, [RouteParameters::ORDER => $order]);

        $this->formRequest($this->requestClass, [RequestKeys::STAFF => $staff->getRouteKey()],
            ['method' => 'post', 'route' => $route])->assertAuthorized();
    }
}
