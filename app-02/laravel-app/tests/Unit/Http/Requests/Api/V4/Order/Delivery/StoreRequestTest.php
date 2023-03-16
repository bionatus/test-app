<?php

namespace Tests\Unit\Http\Requests\Api\V4\Order\Delivery;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V4\Order\DeliveryController;
use App\Http\Requests\Api\V4\Order\Delivery\CurriDeliveryRequest;
use App\Http\Requests\Api\V4\Order\Delivery\PickupRequest;
use App\Http\Requests\Api\V4\Order\Delivery\ShipmentDeliveryRequest;
use App\Http\Requests\Api\V4\Order\Delivery\StoreRequest;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use App\Models\SupplierHour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Carbon;
use Lang;
use Mockery;
use Tests\Unit\Http\Requests\RequestTestCase;
use URL;

/** @see DeliveryController */
class StoreRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = StoreRequest::class;
    private string   $route;

    public function setUp(): void
    {
        parent::setUp();
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order    = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $date     = Carbon::createFromFormat('Y-m-d', '2023-02-10');
        SupplierHour::factory()->usingSupplier($supplier)->create([
            'day'  => strtolower($date->format('l')),
            'from' => $date->clone()->startOfDay()->format('h:i a'),
            'to'   => $date->clone()->endOfDay()->format('h:i a'),
        ]);
        $this->login($user);
        $this->route = URL::route(RouteNames::API_V4_ORDER_DELIVERY_STORE, [RouteParameters::ORDER => $order]);
        $this->order = $order;
    }

    /** @test */
    public function it_should_authorize()
    {
        $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route])->assertAuthorized();
    }

    /** @test */
    public function its_type_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $request->assertValidationMessages([
            Lang::get('validation.required', ['attribute' => $this->getDisplayableAttribute(RequestKeys::TYPE)]),
        ]);
    }

    /** @test */
    public function its_type_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TYPE => 1],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $request->assertValidationMessages([
            Lang::get('validation.string', ['attribute' => $this->getDisplayableAttribute(RequestKeys::TYPE)]),
        ]);
    }

    /**
     * @test
     * @dataProvider typeProvider
     */
    public function its_type_must_be_valid($shouldPass, $type)
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::TYPE => $type],
            ['method' => 'post', 'route' => $this->route]);

        if ($shouldPass) {
            $request->assertValidationErrorsMissing([RequestKeys::TYPE]);

            return;
        }

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::TYPE]);
        $request->assertValidationMessages([
            Lang::get('validation.in', ['attribute' => $this->getDisplayableAttribute(RequestKeys::TYPE)]),
        ]);
    }

    public function typeProvider(): array
    {
        return [
            [false, 'invalid'],
            [true, OrderDelivery::TYPE_CURRI_DELIVERY],
            [true, OrderDelivery::TYPE_PICKUP],
            [true, OrderDelivery::TYPE_SHIPMENT_DELIVERY],
        ];
    }

    /** @test */
    public function its_is_needed_now_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IS_NEEDED_NOW]);
        $request->assertValidationMessages([
            Lang::get('validation.required',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::IS_NEEDED_NOW)]),
        ]);
    }

    /** @test */
    public function its_is_needed_now_should_be_boolean()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::IS_NEEDED_NOW => 'string',
        ], [
            'method' => 'post',
            'route'  => $this->route,
        ]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::IS_NEEDED_NOW]);
        $request->assertValidationMessages([
            Lang::get('validation.boolean',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::IS_NEEDED_NOW)]),
        ]);
    }

    /**
     * @test
     * @dataProvider requestTypeProvider
     */
    public function it_uses_specific_rules_based_on_delivery_type($deliveryRequest, $requestType)
    {
        $route = Mockery::mock(Route::class);
        $route->shouldReceive('parameter')->withArgs([RouteParameters::ORDER, null])->andReturn($this->order);

        $specificRequest = $deliveryRequest::create('', 'POST', []);
        $specificRequest->setRouteResolver(fn() => $route);
        $specificRules = $specificRequest->rules();

        $request = StoreRequest::create('', 'POST', [
            RequestKeys::TYPE => $requestType,
        ]);
        $request->setRouteResolver(fn() => $route);

        $this->assertArrayHasKeysAndValues($specificRules, $request->rules());
    }

    public function requestTypeProvider(): array
    {
        return [
            [CurriDeliveryRequest::class, OrderDelivery::TYPE_CURRI_DELIVERY],
            [PickupRequest::class, OrderDelivery::TYPE_PICKUP],
            [ShipmentDeliveryRequest::class, OrderDelivery::TYPE_SHIPMENT_DELIVERY],
        ];
    }

    /** @test */
    public function it_pass_on_valid_values()
    {
        $date = '2023-02-10';
        Carbon::setTestNow($date . ' 09:29:00');
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE                 => OrderDelivery::TYPE_CURRI_DELIVERY,
            RequestKeys::REQUESTED_START_TIME => Carbon::createFromTime(9)->format('H:i'),
            RequestKeys::REQUESTED_END_TIME   => Carbon::createFromTime(10)->format('H:i'),
            RequestKeys::REQUESTED_DATE       => $date,
            RequestKeys::IS_NEEDED_NOW        => false,
        ], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationPassed();
    }
}
