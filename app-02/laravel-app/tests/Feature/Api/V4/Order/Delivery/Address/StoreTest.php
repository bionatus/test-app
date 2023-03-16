<?php

namespace Tests\Feature\Api\V4\Order\Delivery\Address;

use App;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Controllers\Api\V4\Order\Delivery\Address\AddressController;
use App\Http\Requests\Api\V4\Order\Delivery\Address\StoreRequest;
use App\Http\Resources\Api\V4\Order\Delivery\BaseResource;
use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\ShipmentDelivery;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Curri\Curri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see AddressController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V4_ORDER_DELIVERY_ADDRESS_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);
        $this->post(URL::route($this->routeName,
            [RouteParameters::ORDER => Order::factory()->createQuietly()->getRouteKey()]));
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /** @test */
    public function it_returns_the_correct_base_resource_schema()
    {
        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->shipmentDelivery()->usingOrder($order)->create();
        ShipmentDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order]);
        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'US-AL',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'Message to the supplier!',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
    }

    /** @test */
    public function it_creates_a_destination_address_if_it_is_not_created()
    {
        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()
            ->shipmentDelivery()
            ->usingOrder($order)
            ->create(['note' => 'old note']);
        ShipmentDelivery::factory()->usingOrderDelivery($orderDelivery)->create(['destination_address_id' => null]);

        $this->assertDatabaseCount(Address::tableName(), 0);

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order]);
        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'US-AL',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'new note',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseCount(Address::tableName(), 1);
        $this->assertDatabaseHas(Address::tableName(), [
            'address_1' => 'Address 1',
            'address_2' => 'Address 2',
            'country'   => 'US',
            'state'     => 'US-AL',
            'city'      => 'New York',
            'zip_code'  => '90001',
        ]);
    }

    /** @test */
    public function it_updates_a_destination_address()
    {
        $user               = User::factory()->create();
        $supplier           = Supplier::factory()->createQuietly();
        $order              = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $orderDelivery      = OrderDelivery::factory()
            ->shipmentDelivery()
            ->usingOrder($order)
            ->create(['note' => 'old note']);
        $destinationAddress = Address::factory()->create();
        ShipmentDelivery::factory()
            ->usingDestinationAddress($destinationAddress)
            ->usingOrderDelivery($orderDelivery)
            ->create();

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order]);
        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'US-AL',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => 'new note',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseCount(Address::tableName(), 1);
        $this->assertDatabaseHas(Address::tableName(), [
            'address_1' => 'Address 1',
            'address_2' => 'Address 2',
            'country'   => 'US',
            'state'     => 'US-AL',
            'city'      => 'New York',
            'zip_code'  => '90001',
        ]);
    }

    /** @test */
    public function it_updates_the_note_of_the_order_delivery()
    {
        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()
            ->shipmentDelivery()
            ->usingOrder($order)
            ->create(['note' => 'old note']);
        ShipmentDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order]);
        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'US-AL',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => $newNote = 'new note',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'note' => $newNote,
        ]);
    }

    /** @test */
    public function it_stores_origin_address_with_the_supplier_address()
    {
        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly([
            'address'   => $address = 'address fake',
            'address_2' => $address2 = 'address 2 fake',
            'city'      => $city = 'city fake',
            'state'     => $state = 'state fake',
            'country'   => $country = 'country fake',
            'zip_code'  => $zipCode = '11111',
        ]);
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create(['note' => 'old note']);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('getQuote')->withAnyArgs()->once()->andReturn([
            'fee'     => 1200,
            'quoteId' => 'abc-123',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order]);
        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'US-AL',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => $newNote = 'new note',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas(Address::tableName(), [
            'address_1' => $address,
            'address_2' => $address2,
            'city'      => $city,
            'state'     => $state,
            'country'   => $country,
            'zip_code'  => $zipCode,
        ]);
    }

    /** @test */
    public function it_updates_origin_and_destination_addresses()
    {
        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly([
            'address'   => $address = 'address fake',
            'address_2' => $address2 = 'address 2 fake',
            'city'      => $city = 'city fake',
            'state'     => $state = 'state fake',
            'country'   => $country = 'country fake',
            'zip_code'  => $zipCode = '11111',
        ]);
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create(['note' => 'old note']);
        $origin        = Address::factory()->create();
        $destination   = Address::factory()->create();
        CurriDelivery::factory()
            ->usingDestinationAddress($destination)
            ->usingOriginAddress($origin)
            ->usingOrderDelivery($orderDelivery)
            ->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('getQuote')->withAnyArgs()->once()->andReturn([
            'fee'     => 1200,
            'quoteId' => 'abc-123',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order]);
        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'US-AL',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => $newNote = 'new note',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseCount(Address::tableName(), 2);
        $this->assertDatabaseHas(Address::tableName(), [
            'address_1' => $address,
            'address_2' => $address2,
            'city'      => $city,
            'state'     => $state,
            'country'   => $country,
            'zip_code'  => $zipCode,
        ]);
    }

    /** @test */
    public function it_updates_curri_quote_information()
    {
        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly([
            'address'   => $address = 'address fake',
            'address_2' => $address2 = 'address 2 fake',
            'city'      => $city = 'city fake',
            'state'     => $state = 'state fake',
            'country'   => $country = 'country fake',
            'zip_code'  => $zipCode = '11111',
        ]);
        $order         = Order::factory()->usingUser($user)->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create(['note' => 'old note']);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('getQuote')->withAnyArgs()->once()->andReturn([
            'fee'     => $fee = 1200,
            'quoteId' => $quoteId = 'abc-123',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $route = URL::route($this->routeName, [RouteParameters::ORDER => $order]);
        $this->login($user);
        $response = $this->post($route, [
            RequestKeys::DESTINATION_ADDRESS_1 => 'Address 1',
            RequestKeys::DESTINATION_ADDRESS_2 => 'Address 2',
            RequestKeys::DESTINATION_COUNTRY   => 'US',
            RequestKeys::DESTINATION_STATE     => 'US-AL',
            RequestKeys::DESTINATION_CITY      => 'New York',
            RequestKeys::DESTINATION_ZIP_CODE  => '90001',
            RequestKeys::NOTE                  => $newNote = 'new note',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas(CurriDelivery::tableName(), [
            'quote_id'     => $quoteId,
            'vehicle_type' => 'car',
        ]);
        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'fee' => $fee,
        ]);
    }
}
