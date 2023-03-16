<?php

namespace Tests\Unit\Http\Requests\Api\V4\Order\Delivery;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V4\Order\DeliveryController;
use App\Http\Requests\Api\V4\Order\Delivery\ShipmentDeliveryRequest;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\ShipmentDeliveryPreference;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Lang;
use Tests\Unit\Http\Requests\RequestTestCase;
use URL;

/** @see DeliveryController */
class ShipmentDeliveryRequestTest extends RequestTestCase
{
    use RefreshDatabase;

    protected string $requestClass = ShipmentDeliveryRequest::class;
    private string   $route;

    public function setUp(): void
    {
        parent::setUp();

        $supplier = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $this->route = URL::route(RouteNames::API_V4_ORDER_DELIVERY_STORE, [RouteParameters::ORDER => $order]);
    }

    /** @test */
    public function its_shipment_delivery_preference_parameter_is_required()
    {
        $request = $this->formRequest($this->requestClass, [], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SHIPMENT_PREFERENCE]);
        $request->assertValidationMessages([
            Lang::get('validation.required',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::SHIPMENT_PREFERENCE)]),
        ]);
    }

    /** @test */
    public function its_shipment_delivery_preference_parameter_must_be_a_string()
    {
        $request = $this->formRequest($this->requestClass, [RequestKeys::SHIPMENT_PREFERENCE => ['array item']],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SHIPMENT_PREFERENCE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::SHIPMENT_PREFERENCE);
        $request->assertValidationMessages([Lang::get('validation.string', ['attribute' => $attribute])]);
    }

    /** @test */
    public function its_shipment_delivery_preference_parameter_must_exist()
    {
        ShipmentDeliveryPreference::factory()->create();

        $request = $this->formRequest($this->requestClass, [
            RequestKeys::TYPE                => OrderDelivery::TYPE_SHIPMENT_DELIVERY,
            RequestKeys::SHIPMENT_PREFERENCE => 'invalid',
        ], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::SHIPMENT_PREFERENCE]);
        $request->assertValidationMessages([
            Lang::get('validation.exists',
                ['attribute' => $this->getDisplayableAttribute(RequestKeys::SHIPMENT_PREFERENCE)]),
        ]);
    }

    /** @test */
    public function it_should_not_get_requested_date()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::REQUESTED_DATE => Carbon::now()->format('Y-m-d'),
        ], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REQUESTED_DATE]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::REQUESTED_DATE);
        $request->assertValidationMessages([Lang::get(':attribute is not allowed.', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_not_get_requested_start_time()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::REQUESTED_START_TIME => '10:00',
        ], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REQUESTED_START_TIME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::REQUESTED_START_TIME);
        $request->assertValidationMessages([Lang::get(':attribute is not allowed.', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_not_get_requested_end_time()
    {
        $request = $this->formRequest($this->requestClass, [
            RequestKeys::REQUESTED_END_TIME => '10:00',
        ], ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationFailed();
        $request->assertValidationErrors([RequestKeys::REQUESTED_END_TIME]);
        $attribute = $this->getDisplayableAttribute(RequestKeys::REQUESTED_END_TIME);
        $request->assertValidationMessages([Lang::get(':attribute is not allowed.', ['attribute' => $attribute])]);
    }

    /** @test */
    public function it_should_pass_on_valid_data()
    {
        ShipmentDeliveryPreference::factory()->create(['slug' => 'overnight']);
        $request = $this->formRequest($this->requestClass, [RequestKeys::SHIPMENT_PREFERENCE => 'overnight'],
            ['method' => 'post', 'route' => $this->route]);

        $request->assertValidationPassed();
    }
}
