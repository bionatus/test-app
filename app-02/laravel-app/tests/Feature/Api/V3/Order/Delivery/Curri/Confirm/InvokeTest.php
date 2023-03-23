<?php

namespace Tests\Feature\Api\V3\Order\Delivery\Curri\Confirm;

use App;
use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Events\Order\Delivery\Curri\Booked;
use App\Events\Order\Delivery\Curri\ConfirmedByUser;
use App\Exceptions\CurriException;
use App\Http\Controllers\Api\V3\Order\Delivery\Curri\ConfirmController;
use App\Http\Requests\Api\V3\Order\Delivery\Curri\Confirm\InvokeRequest;
use App\Http\Resources\Api\V3\Order\BaseResource;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Curri\Curri;
use Carbon\Carbon;
use DB;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;
use URL;

/** @see ConfirmController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    private string $routeName = RouteNames::API_V3_ORDER_DELIVERY_CURRI_CONFIRM_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName, [RouteParameters::ORDER => $order->getRouteKey()]));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:confirmCurriOrder,' . RouteParameters::ORDER]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, InvokeRequest::class);
    }

    /** @test */
    public function it_books_a_curri_delivery()
    {
        Event::fake(ConfirmedByUser::class);

        Carbon::setTestNow('2022-11-10 02:00PM');
        $date = Carbon::now()->format('Y-m-d');

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order         = Order::factory()->approved()->usingSupplier($supplier)->usingUser($user)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => $date,
            'start_time' => Carbon::createFromTime(15)->format('H:i'),
            'end_time'   => Carbon::createFromTime(18)->format('H:i'),
        ]);

        $curriDelivery = CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->confirmedBySupplier()->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('bookDelivery')->withAnyArgs()->once()->andReturn([
            'id'          => $bookId = 'book id',
            'price'       => 1200,
            'tracking_id' => $trackingId = 'tracking id',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $this->login($user);
        $route    = URL::route($this->routeName, $order);
        $response = $this->post($route, [
            RequestKeys::START_TIME => '15:00',
            RequestKeys::END_TIME   => '18:00',
            RequestKeys::DATE       => $date,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertSame($bookId, $response->json('data.delivery.info.book_id'));
        $this->assertDatabaseHas(CurriDelivery::tableName(), [
            'id'          => $curriDelivery->getKey(),
            'book_id'     => $bookId,
            'tracking_id' => $trackingId,
        ]);
    }

    /** @test */
    public function it_completes_the_order()
    {
        Event::fake(ConfirmedByUser::class);
        Carbon::setTestNow('2022-11-10 02:00PM');
        $date = Carbon::now()->format('Y-m-d');

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order         = Order::factory()->approved()->usingSupplier($supplier)->usingUser($user)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => $date,
            'start_time' => Carbon::createFromTime(15)->format('H:i'),
            'end_time'   => Carbon::createFromTime(18)->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->confirmedBySupplier()->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('bookDelivery')->withAnyArgs()->once()->andReturn([
            'id'          => 'book id',
            'price'       => 1200,
            'tracking_id' => 'tracking id',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $this->login($user);
        $route    = URL::route($this->routeName, $order);
        $response = $this->post($route, [
            RequestKeys::START_TIME => Carbon::createFromTime(15)->format('H:i'),
            RequestKeys::END_TIME   => Carbon::createFromTime(18)->format('H:i'),
            RequestKeys::DATE       => $date,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertSame(Substatus::STATUS_COMPLETED_DONE, $order->refresh()->lastStatus->substatus_id);
    }

    /** @test */
    public function it_confirms_a_curri_delivery_order()
    {
        Event::fake(ConfirmedByUser::class);
        Carbon::setTestNow('2022-11-10 02:00PM');
        $date = Carbon::now()->format('Y-m-d');

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order         = Order::factory()->approved()->usingSupplier($supplier)->usingUser($user)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => $date,
            'start_time' => Carbon::createFromTime(15)->format('H:i'),
            'end_time'   => Carbon::createFromTime(18)->format('H:i'),
        ]);
        $curriDelivery = CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->confirmedBySupplier()->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('bookDelivery')->withAnyArgs()->once()->andReturn([
            'id'          => 'book id',
            'price'       => 1200,
            'tracking_id' => 'tracking id',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $this->login($user);
        $route    = URL::route($this->routeName, $order);
        $response = $this->post($route, [
            RequestKeys::START_TIME => Carbon::createFromTime(15)->format('H:i'),
            RequestKeys::END_TIME   => Carbon::createFromTime(18)->format('H:i'),
            RequestKeys::DATE       => $date,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $this->assertNotNull($curriDelivery->refresh()->user_confirmed_at);
    }

    /** @test */
    public function it_dispatches_a_curri_confirmed_event()
    {
        Event::fake(ConfirmedByUser::class);
        Carbon::setTestNow('2022-11-10 02:00PM');
        $date = Carbon::now()->format('Y-m-d');

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order         = Order::factory()->approved()->usingSupplier($supplier)->usingUser($user)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => $date,
            'start_time' => Carbon::createFromTime(15)->format('H:i'),
            'end_time'   => Carbon::createFromTime(18)->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->confirmedBySupplier()->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('bookDelivery')->withAnyArgs()->once()->andReturn([
            'id'          => 'book id',
            'price'       => 1200,
            'tracking_id' => 'tracking id',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $this->login($user);
        $route    = URL::route($this->routeName, $order);
        $response = $this->post($route, [
            RequestKeys::START_TIME => Carbon::createFromTime(15)->format('H:i'),
            RequestKeys::END_TIME   => Carbon::createFromTime(18)->format('H:i'),
            RequestKeys::DATE       => $date,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched(ConfirmedByUser::class);
    }

    /** @test */
    public function it_dispatches_a_curri_booked_event()
    {
        Event::fake(Booked::class);
        Carbon::setTestNow('2022-11-10 02:00PM');
        $date = Carbon::now()->format('Y-m-d');

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order         = Order::factory()->approved()->usingSupplier($supplier)->usingUser($user)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => $date,
            'start_time' => Carbon::createFromTime(15)->format('H:i'),
            'end_time'   => Carbon::createFromTime(18)->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->confirmedBySupplier()->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('bookDelivery')->withAnyArgs()->once()->andReturn([
            'id'          => 'book id',
            'price'       => 1200,
            'tracking_id' => 'tracking id',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $this->login($user);
        $route    = URL::route($this->routeName, $order);
        $response = $this->post($route, [
            RequestKeys::START_TIME => Carbon::createFromTime(15)->format('H:i'),
            RequestKeys::END_TIME   => Carbon::createFromTime(18)->format('H:i'),
            RequestKeys::DATE       => $date,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched(Booked::class);
    }

    /** @test */
    public function it_returns_an_http_failed_dependency_on_curri_client_error()
    {
        Event::fake(ConfirmedByUser::class);
        Carbon::setTestNow('2022-11-10 04:12PM');
        $date = Carbon::now()->format('Y-m-d');

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order         = Order::factory()->approved()->usingSupplier($supplier)->usingUser($user)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => $date,
            'start_time' => Carbon::createFromTime(15)->format('H:i'),
            'end_time'   => Carbon::createFromTime(18)->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->confirmedBySupplier()->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('bookDelivery')
            ->withAnyArgs()
            ->once()
            ->andThrow(new CurriException($exceptionMessage = 'foo:bar'));
        App::bind(Curri::class, fn() => $curri);

        $this->login($user);
        $route    = URL::route($this->routeName, $order);
        $response = $this->post($route, [
            RequestKeys::START_TIME => Carbon::createFromTime(15)->format('H:i'),
            RequestKeys::END_TIME   => Carbon::createFromTime(18)->format('H:i'),
            RequestKeys::DATE       => $date,
        ]);
        $response->assertStatus(Response::HTTP_FAILED_DEPENDENCY);

        $message = $response->json('message');
        $this->assertSame($exceptionMessage, $message);
    }

    /** @test */
    public function it_uses_the_current_hour_if_current_time_is_in_the_selected_time_range()
    {
        Event::fake(ConfirmedByUser::class);
        Carbon::setTestNow('2022-11-10 04:12PM');
        $date      = Carbon::now()->format('Y-m-d');
        $startTime = Carbon::createFromTime(15)->startOfHour();
        $endTime   = Carbon::createFromTime(18)->startOfHour();

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order         = Order::factory()->approved()->usingSupplier($supplier)->usingUser($user)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => Carbon::now(),
            'start_time' => $startTime->format('H:i'),
            'end_time'   => $endTime->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->confirmedBySupplier()->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('bookDelivery')->withAnyArgs()->once()->andReturn([
            'id'          => 'book id',
            'price'       => 1200,
            'tracking_id' => 'tracking id',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $this->login($user);
        $route    = URL::route($this->routeName, $order);
        $response = $this->post($route, [
            RequestKeys::START_TIME => $startTime->format('H:i'),
            RequestKeys::END_TIME   => $endTime->format('H:i'),
            RequestKeys::DATE       => $date,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $dbFormat = 'H:i:s';
        if (DB::connection()->getName() == 'sqlite') {
            $dbFormat = 'H:i';
        }

        $this->assertDatabaseHas(OrderDelivery::tableName(), [
            'id'                   => $orderDelivery->getKey(),
            'start_time'           => Carbon::now()->startOfHour()->format($dbFormat),
            'end_time'             => $endTime->format($dbFormat),
            'date'                 => '2022-11-10',
            'requested_date'       => '2022-11-10',
            'requested_start_time' => Carbon::now()->startOfHour()->format($dbFormat),
            'requested_end_time'   => $endTime->format($dbFormat),
        ]);
    }

    /** @test */
    public function it_books_a_curri_delivery_even_when_is_not_confirmed_by_supplier()
    {
        Event::fake(ConfirmedByUser::class);

        Carbon::setTestNow('2022-11-10 02:00PM');
        $date = Carbon::now()->format('Y-m-d');

        $user          = User::factory()->create();
        $supplier      = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order         = Order::factory()->approved()->usingSupplier($supplier)->usingUser($user)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => $date,
            'start_time' => Carbon::createFromTime(15)->format('H:i'),
            'end_time'   => Carbon::createFromTime(18)->format('H:i'),
        ]);

        $curriDelivery = CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $curri = Mockery::mock(Curri::class);
        $curri->shouldReceive('bookDelivery')->withAnyArgs()->once()->andReturn([
            'id'          => $bookId = 'book id',
            'price'       => 1200,
            'tracking_id' => $trackingId = 'tracking id',
        ]);
        App::bind(Curri::class, fn() => $curri);

        $this->login($user);
        $route    = URL::route($this->routeName, $order);
        $response = $this->post($route, [
            RequestKeys::START_TIME => '15:00',
            RequestKeys::END_TIME   => '18:00',
            RequestKeys::DATE       => $date,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertSame($bookId, $response->json('data.delivery.info.book_id'));
        $this->assertDatabaseHas(CurriDelivery::tableName(), [
            'id'          => $curriDelivery->getKey(),
            'book_id'     => $bookId,
            'tracking_id' => $trackingId,
        ]);
    }
}
