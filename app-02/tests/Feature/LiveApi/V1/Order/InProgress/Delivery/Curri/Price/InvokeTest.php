<?php

namespace Tests\Feature\LiveApi\V1\Order\InProgress\Delivery\Curri\Price;

use App;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Exceptions\CurriException;
use App\Http\Controllers\LiveApi\V1\Order\InProgress\Delivery\Curri\PriceController;
use App\Http\Requests\LiveApi\V1\Order\InProgress\Delivery\Curri\Price\InvokeRequest;
use App\Http\Resources\LiveApi\V1\Order\InProgress\Delivery\Curri\Price\BaseResource;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Staff;
use App\Models\Supplier;
use App\Services\Curri\Curri;
use Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see PriceController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::LIVE_API_V1_ORDER_IN_PROGRESS_DELIVERY_CURRI_PRICE_STORE;

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
        $this->assertRouteUsesMiddleware($this->routeName, ['can:getCurriDeliveryPrice,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /**
     * @test
     * @dataProvider addressProvider
     */
    public function it_display_an_order_delivery_with_its_fee_updated(bool $useSupplierAddress)
    {
        $supplier      = Supplier::factory()->createQuietly([
            'address'   => 'supplier address',
            'address_2' => 'supplier address 2',
            'city'      => 'supplier city',
            'state'     => 'supplier state',
            'zip_code'  => '11111',
            'country'   => 'supplier country',
        ]);
        $staff         = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->approved()->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);
        $orderDelivery = OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create([
            'date'       => Carbon::now()->format('Y-m-d'),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
            'fee'        => 1000,
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create(['vehicle_type' => 'car']);

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('getQuote')->withAnyArgs()->once()->andReturn([
            'fee'     => 1200,
            'quoteId' => 'abc-123',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $route = URL::route($this->routeName, $order);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->post($route, [
            RequestKeys::USE_STORE_ADDRESS => (int) $useSupplierAddress,
            RequestKeys::VEHICLE_TYPE      => 'car',
            RequestKeys::ADDRESS           => 'address',
            RequestKeys::ADDRESS_2         => 'address 2',
            RequestKeys::CITY              => 'city',
            RequestKeys::STATE             => 'state',
            RequestKeys::ZIP_CODE          => '22222',
            RequestKeys::COUNTRY           => 'country',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $schema = $this->jsonSchema(BaseResource::jsonSchema());
        $this->validateResponseSchema($schema, $response);
        $this->assertSame(12, json_decode($response->getContent())->data->fee);
    }

    public function addressProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /** @test */
    public function it_returns_an_http_failed_dependency_on_curri_client_error()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $staff         = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->approved()->usingSupplier($supplier)->create([
            'working_on_it' => 'John Doe',
        ]);
        $orderDelivery = OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create([
            'date' => Carbon::now()->format('Y-m-d'),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
            'fee'  => 1000,
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create(['vehicle_type' => 'car']);

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('getQuote')
            ->withAnyArgs()
            ->once()
            ->andThrow(new CurriException($exceptionMessage = 'foo:bar'));
        App::bind(Curri::class, fn() => $curri);

        $route = URL::route($this->routeName, $order);

        Auth::shouldUse('live');
        $this->login($staff);

        $response = $this->post($route, [
            RequestKeys::USE_STORE_ADDRESS => false,
            RequestKeys::VEHICLE_TYPE      => 'car',
            RequestKeys::ADDRESS           => 'address',
            RequestKeys::ADDRESS_2         => 'address 2',
            RequestKeys::CITY              => 'city',
            RequestKeys::STATE             => 'state',
            RequestKeys::ZIP_CODE          => '22222',
            RequestKeys::COUNTRY           => 'country',
        ]);

        $response->assertStatus(Response::HTTP_FAILED_DEPENDENCY);

        $message = $response->json('message');
        $this->assertSame($exceptionMessage, $message);
    }
}
