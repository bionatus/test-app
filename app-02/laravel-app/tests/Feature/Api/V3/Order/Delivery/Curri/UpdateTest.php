<?php

namespace Tests\Feature\Api\V3\Order\Delivery\Curri;

use App;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\Delivery\Curri\ConfirmedByUser;
use App\Http\Requests\Api\V3\Order\Delivery\Curri\UpdateRequest;
use App\Http\Resources\Api\V3\Order\BaseResource;
use App\Jobs\Order\Delivery\Curri\LegacyDelayBooking;
use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use App\Services\Curri\Curri;
use Bus;
use DB;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see CurriController */
class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use WithLatamMiddlewares;

    private string        $routeName = RouteNames::API_V3_ORDER_DELIVERY_CURRI_UPDATE;
    private Supplier      $supplier;
    private Order         $order;
    private OrderDelivery $orderDelivery;
    private CurriDelivery $curriDelivery;

    public function setUp(): void
    {
        parent::setUp();

        $this->supplier      = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $this->order         = Order::factory()->usingSupplier($this->supplier)->approved()->create();
        $this->orderDelivery = OrderDelivery::factory()->usingOrder($this->order)->curriDelivery()->create([
            'date'       => Carbon::now(),
            'start_time' => Carbon::createFromTime(15)->format('H:i'),
            'end_time'   => Carbon::createFromTime(18)->format('H:i'),
            'fee'        => 1000,
        ]);
        $this->curriDelivery = CurriDelivery::factory()
            ->usingOrderDelivery($this->orderDelivery)
            ->confirmedBySupplier()
            ->create(['quote_id' => 'xyz-123']);
    }

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->patch(URL::route($this->routeName, $this->order));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:confirmCurriOrder,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, UpdateRequest::class);
    }

    /** @test */
    public function it_updates_the_delivery_address_and_eta()
    {
        Carbon::setTestNow('2022-11-10 02:00PM');

        $date      = Carbon::now()->format('Y-m-d');
        $startTime = Carbon::createFromTime(15);
        $endTime   = Carbon::createFromTime(18);

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('getQuote')->withAnyArgs()->once()->andReturn([
            'fee'     => $fee = 1200,
            'quoteId' => $quoteId = 'abc-123',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $route = URL::route($this->routeName, $this->order);
        $this->login($this->order->user);
        $response = $this->patch($route, [
            RequestKeys::ADDRESS    => $address = '1234 fake st.',
            RequestKeys::ADDRESS_2  => $address2 = 'unit 2',
            RequestKeys::COUNTRY    => $country = 'US',
            RequestKeys::STATE      => $state = 'california',
            RequestKeys::ZIP_CODE   => $zip = '12345',
            RequestKeys::CITY       => $city = 'some city',
            RequestKeys::START_TIME => $startTime->format('H:i'),
            RequestKeys::END_TIME   => $endTime->format('H:i'),
            RequestKeys::DATE       => $date,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $schema = $this->jsonSchema(BaseResource::jsonSchema());
        $this->validateResponseSchema($schema, $response);

        $data            = Collection::make($response->json('data'));
        $delivery        = Collection::make($response->json('data.delivery'));
        $responseAddress = Collection::make($response->json('data.delivery.info.destination_address'));

        $this->assertSame($data['id'], $this->order->getRouteKey());
        $this->assertSame($delivery['requested_date'], $date);
        $this->assertSame($delivery['date'], $date);
        $this->assertSame($delivery['requested_start_time'], $startTime->format('H:i'));
        $this->assertSame($delivery['requested_end_time'], $endTime->format('H:i'));
        $this->assertSame($delivery['start_time'], $startTime->format('H:i'));
        $this->assertSame($delivery['end_time'], $endTime->format('H:i'));
        $this->assertSame($delivery['fee'], 12);
        $this->assertSame($delivery['info']['quote_id'], $quoteId);

        $this->assertSame($responseAddress['address_1'], $address);
        $this->assertSame($responseAddress['address_2'], $address2);
        $this->assertSame($responseAddress['city'], $city);
        $this->assertSame($responseAddress['state'], $state);
        $this->assertSame($responseAddress['country'], $country);
        $this->assertSame($responseAddress['zip_code'], $zip);

        $dbFormat = 'H:i:s';
        if (DB::connection()->getName() == 'sqlite') {
            $dbFormat = 'H:i';
        }

        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'id'                   => $this->orderDelivery->getKey(),
            'order_id'             => $this->order->getKey(),
            'requested_date'       => $date,
            'date'                 => $date,
            'requested_start_time' => $startTime->format($dbFormat),
            'requested_end_time'   => $endTime->format($dbFormat),
            'start_time'           => $startTime->format($dbFormat),
            'end_time'             => $endTime->format($dbFormat),
            'fee'                  => $fee,
        ]);

        $this->assertDatabaseHas(CurriDelivery::tableName(), [
            'id'       => $this->curriDelivery->getKey(),
            'quote_id' => $quoteId,
        ]);

        $this->assertDatabaseHas(Address::tableName(), [
            'id'        => $this->curriDelivery->destination_address_id,
            'address_1' => $address,
            'address_2' => $address2,
            'city'      => $city,
            'state'     => $state,
            'country'   => $country,
            'zip_code'  => $zip,
        ]);
    }

    /** @test */
    public function it_uses_the_current_hour_if_current_time_is_in_the_selected_time_range()
    {
        Carbon::setTestNow('2022-11-10 04:12PM');

        $date = Carbon::now()->format('Y-m-d');

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('getQuote')->withAnyArgs()->once()->andReturn([
            'fee'     => $fee = 1200,
            'quoteId' => $quoteId = 'abc-123',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $this->login($this->order->user);
        $route    = URL::route($this->routeName, $this->order);
        $response = $this->patch($route, [
            RequestKeys::ADDRESS    => '1234 fake st.',
            RequestKeys::ADDRESS_2  => 'unit 2',
            RequestKeys::COUNTRY    => 'US',
            RequestKeys::STATE      => 'california',
            RequestKeys::ZIP_CODE   => '12345',
            RequestKeys::CITY       => 'some city',
            RequestKeys::START_TIME => Carbon::createFromTime(15)->format('H:i'),
            RequestKeys::END_TIME   => Carbon::createFromTime(18)->format('H:i'),
            RequestKeys::DATE       => $date,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $schema = $this->jsonSchema(BaseResource::jsonSchema());
        $this->validateResponseSchema($schema, $response);

        $data              = Collection::make($response->json('data'));
        $delivery          = Collection::make($response->json('data.delivery'));
        $expectedStartTime = Carbon::createFromTime(16)->startOfHour();
        $expectedEndTime   = Carbon::createFromTime(18)->startOfHour();

        $this->assertSame($data['id'], $this->order->getRouteKey());
        $this->assertSame($delivery['requested_date'], $date);
        $this->assertSame($delivery['date'], $date);
        $this->assertSame($delivery['requested_start_time'], $expectedStartTime->format('H:i'));
        $this->assertSame($delivery['requested_end_time'], $expectedEndTime->format('H:i'));
        $this->assertSame($delivery['start_time'], $expectedStartTime->format('H:i'));
        $this->assertSame($delivery['end_time'], $expectedEndTime->format('H:i'));
        $this->assertSame($delivery['fee'], 12);
        $this->assertSame($delivery['info']['quote_id'], $quoteId);

        $dbFormat = 'H:i:s';
        if (DB::connection()->getName() == 'sqlite') {
            $dbFormat = 'H:i';
        }

        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'id'                   => $this->orderDelivery->getKey(),
            'order_id'             => $this->order->getKey(),
            'requested_date'       => $date,
            'date'                 => $date,
            'requested_start_time' => $expectedStartTime->format($dbFormat),
            'requested_end_time'   => $expectedEndTime->format($dbFormat),
            'start_time'           => $expectedStartTime->format($dbFormat),
            'end_time'             => $expectedEndTime->format($dbFormat),
            'fee'                  => $fee,
        ]);
    }

    /** @test */
    public function it_dispatches_a_curri_confirmed_event()
    {
        Event::fake(ConfirmedByUser::class);

        $route = URL::route($this->routeName, $this->order);
        Carbon::setTestNow('2022-11-10 04:12PM');
        $date = Carbon::now()->format('Y-m-d');

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('getQuote')->withAnyArgs()->once()->andReturn([
            'fee'     => 1200,
            'quoteId' => 'abc-123',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $this->login($this->order->user);
        $response = $this->patch($route, [
            RequestKeys::ADDRESS    => '1234 fake st.',
            RequestKeys::ADDRESS_2  => 'unit 2',
            RequestKeys::COUNTRY    => 'US',
            RequestKeys::STATE      => 'california',
            RequestKeys::ZIP_CODE   => '12345',
            RequestKeys::CITY       => 'some city',
            RequestKeys::START_TIME => '15:00',
            RequestKeys::END_TIME   => '18:00',
            RequestKeys::DATE       => $date,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        Event::assertDispatched(ConfirmedByUser::class);
    }

    /** @test */
    public function it_dispatches_the_delay_booking_job()
    {
        Bus::fake();

        $route = URL::route($this->routeName, $this->order);
        Carbon::setTestNow('2022-11-10 04:12PM');
        $date = Carbon::now()->format('Y-m-d');

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('getQuote')->withAnyArgs()->once()->andReturn([
            'fee'     => 1200,
            'quoteId' => 'abc-123',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $this->login($this->order->user);
        $response = $this->patch($route, [
            RequestKeys::ADDRESS    => '1234 fake st.',
            RequestKeys::ADDRESS_2  => 'unit 2',
            RequestKeys::COUNTRY    => 'US',
            RequestKeys::STATE      => 'california',
            RequestKeys::ZIP_CODE   => '12345',
            RequestKeys::CITY       => 'some city',
            RequestKeys::START_TIME => '15:00',
            RequestKeys::END_TIME   => '18:00',
            RequestKeys::DATE       => $date,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        Bus::assertDispatched(LegacyDelayBooking::class);
    }
}
