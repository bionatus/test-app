<?php

namespace Tests\Unit\Services\Curri;

use App;
use App\Exceptions\CurriException;
use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Curri\Curri;
use Carbon\CarbonInterface;
use Config;
use Exception;
use GraphQL\Client;
use GraphQL\Exception\QueryError;
use GraphQL\Results;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class CurriTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_throws_exception_when_no_credentials_are_provided()
    {
        Config::set('curri.user_id');
        Config::set('curri.api_key');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Credentials are required to create a Client');
        new Curri();
    }

    /** @test */
    public function it_throws_exception_when_no_api_endpoint_is_provided()
    {
        Config::set('curri.user_id', 'user id');
        Config::set('curri.api_key', 'api key');
        Config::set('curri.api_endpoint');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Endpoint is required to create a Client');
        new Curri();
    }

    /** @test */
    public function it_gets_a_curri_quote()
    {
        Config::set('curri.user_id', 'user id');
        Config::set('curri.api_key', 'api key');
        Config::set('curri.api_endpoint', 'endpoint');

        $client             = Mockery::mock(Client::class);
        $result             = Mockery::mock(Results::class);
        $originAddress      = Mockery::mock(Address::class);
        $destinationAddress = Mockery::mock(Address::class);

        $originAddress->shouldReceive('getAttribute')->with('address_1')->twice()->andReturnNull();
        $originAddress->shouldReceive('getAttribute')->with('address_2')->once()->andReturnNull();
        $originAddress->shouldReceive('getAttribute')->with('city')->once()->andReturnNull();
        $originAddress->shouldReceive('getAttribute')->with('state')->once()->andReturnNull();
        $originAddress->shouldReceive('getAttribute')->with('zip_code')->once()->andReturnNull();

        $destinationAddress->shouldReceive('getAttribute')->with('address_1')->twice()->andReturnNull();
        $destinationAddress->shouldReceive('getAttribute')->with('city')->once()->andReturnNull();
        $destinationAddress->shouldReceive('getAttribute')->with('state')->once()->andReturnNull();
        $destinationAddress->shouldReceive('getAttribute')->with('zip_code')->once()->andReturnNull();

        $client->shouldReceive('runQuery')->withAnyArgs()->once()->andReturns($result);
        App::bind(Client::class, fn() => $client);

        $result->shouldReceive('getData')->withNoArgs()->twice()->andReturn((object) [
            'deliveryQuote' => (object) [
                'fee' => $fee = 12345,
                'id'  => $quoteId = 'quote_id',
            ],
        ]);

        $curri    = new Curri();
        $response = $curri->getQuote($destinationAddress, $originAddress, 'car');
        $expected = [
            'fee'     => $fee,
            'quoteId' => $quoteId,
        ];

        $this->assertEquals($expected, $response);
    }

    /** @test */
    public function it_throws_an_exception_on_client_fail_getting_a_quote()
    {
        Config::set('curri.user_id', 'user id');
        Config::set('curri.api_key', 'api key');
        Config::set('curri.api_endpoint', 'endpoint');

        $client             = Mockery::mock(Client::class);
        $originAddress      = Mockery::mock(Address::class);
        $destinationAddress = Mockery::mock(Address::class);

        $originAddress->shouldReceive('getAttribute')->with('address_1')->twice()->andReturnNull();
        $originAddress->shouldReceive('getAttribute')->with('address_2')->once()->andReturnNull();
        $originAddress->shouldReceive('getAttribute')->with('city')->once()->andReturnNull();
        $originAddress->shouldReceive('getAttribute')->with('state')->once()->andReturnNull();
        $originAddress->shouldReceive('getAttribute')->with('zip_code')->once()->andReturnNull();

        $destinationAddress->shouldReceive('getAttribute')->with('address_1')->twice()->andReturnNull();
        $destinationAddress->shouldReceive('getAttribute')->with('city')->once()->andReturnNull();
        $destinationAddress->shouldReceive('getAttribute')->with('state')->once()->andReturnNull();
        $destinationAddress->shouldReceive('getAttribute')->with('zip_code')->once()->andReturnNull();

        $client->shouldReceive('runQuery')
            ->withAnyArgs()
            ->once()
            ->andThrow(new QueryError(['errors' => [['message' => $errorMessage = 'error message']]]));
        App::bind(Client::class, fn() => $client);

        $this->expectException(CurriException::class);
        $this->expectExceptionMessage($errorMessage);

        $curri = new Curri();
        $curri->getQuote($destinationAddress, $originAddress, 'car');
    }

    /** @test */
    public function it_books_a_curri_delivery_for_the_next_day()
    {
        Config::set('curri.user_id', 'user id');
        Config::set('curri.api_key', 'api key');
        Config::set('curri.api_endpoint', 'endpoint');

        $accountant         = Mockery::mock(Staff::class);
        $client             = Mockery::mock(Client::class);
        $result             = Mockery::mock(Results::class);
        $curriDelivery      = Mockery::mock(CurriDelivery::class);
        $delivery           = Mockery::mock(OrderDelivery::class);
        $order              = Mockery::mock(Order::class);
        $supplier           = Mockery::mock(Supplier::class);
        $user               = Mockery::mock(User::class);
        $originAddress      = Mockery::mock(Address::class);
        $destinationAddress = Mockery::mock(Address::class);

        $client->shouldReceive('runQuery')->withAnyArgs()->once()->andReturns($result);
        App::bind(Client::class, fn() => $client);

        $result->shouldReceive('getData')->withNoArgs()->times(3)->andReturn((object) [
            'bookDelivery' => (object) [
                'id'         => $bookId = 'book id',
                'price'      => $price = '1433',
                'trackingId' => $trackingId = 'tracking id',
            ],
        ]);

        $delivery->shouldReceive('getAttribute')->with('date')->once()->andReturn(Carbon::now()->addDay());
        $delivery->shouldReceive('getAttribute')->with('time_range')->once()->andReturn('9AM - 12PM');
        $delivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($curriDelivery);
        $delivery->shouldReceive('getAttribute')->with('order')->twice()->andReturn($order);
        $delivery->shouldReceive('getAttribute')->with('note')->once()->andReturn('Note delivery Lorem Ipsum');

        $originAddress->shouldReceive('getAttribute')->with('address_1')->once()->andReturn('fake address 1');
        $originAddress->shouldReceive('getAttribute')->with('address_2')->once()->andReturn('fake address 2');
        $originAddress->shouldReceive('getAttribute')->with('city')->once()->andReturn('fake city');
        $originAddress->shouldReceive('getAttribute')->with('state')->once()->andReturn('fake state');
        $originAddress->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('fake zip code');

        $destinationAddress->shouldReceive('getAttribute')->with('address_1')->once()->andReturn('fake address 1');
        $destinationAddress->shouldReceive('getAttribute')->with('address_2')->once()->andReturn('fake address 2');
        $destinationAddress->shouldReceive('getAttribute')->with('city')->once()->andReturn('fake city');
        $destinationAddress->shouldReceive('getAttribute')->with('state')->once()->andReturn('fake state');
        $destinationAddress->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('fake zip code');

        $curriDelivery->shouldReceive('getAttribute')->with('vehicle_type')->once()->andReturn('car');
        $curriDelivery->shouldReceive('getAttribute')->with('originAddress')->once()->andReturn($originAddress);
        $curriDelivery->shouldReceive('getAttribute')
            ->with('destinationAddress')
            ->once()
            ->andReturn($destinationAddress);

        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('count')->withNoArgs()->once()->andReturn(7);

        $order->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);
        $order->shouldReceive('getAttribute')->with('supplier')->once()->andReturn($supplier);
        $order->shouldReceive('getAttribute')->with('name')->times(3)->andReturn('Order Name');
        $order->shouldReceive('activeItemOrders')->withNoArgs()->once()->andReturn($hasMany);
        $order->shouldReceive('getAttribute')->with('bid_number')->times(3)->andReturn('Bid Number');

        $user->shouldReceive('fullName')->once()->andReturn('john doe');
        $user->shouldReceive('getPhone')->withNoArgs()->once()->andReturn('1234567');
        $user->shouldReceive('companyName')->withNoArgs()->twice()->andReturn('Company name');

        $accountant->shouldReceive('getAttribute')->with('email')->once()->andReturn('accountant@supplier.com');

        $supplier->shouldReceive('getAttribute')->with('name')->once()->andReturn('John Doe');
        $supplier->shouldReceive('getAttribute')->with('contact_name')->once()->andReturn('will smith');
        $supplier->shouldReceive('getAttribute')->with('phone')->once()->andReturn('7654321');
        $supplier->shouldReceive('getAttribute')->with('timezone')->once()->andReturn('America/Los_Angeles');
        $supplier->shouldReceive('getAttribute')->with('name')->twice()->andReturn('Name Supplier Lorem');
        $supplier->shouldReceive('getAttribute')->with('accountant')->once()->andReturn($accountant);
        $supplier->shouldReceive('getKey')->once()->andReturn('1111');

        $curri    = new Curri();
        $response = $curri->bookDelivery($delivery);
        $expected = [
            'id'          => $bookId,
            'price'       => $price,
            'tracking_id' => $trackingId,
        ];

        $this->assertEquals($expected, $response);
    }

    /** @test */
    public function it_books_a_curri_delivery_inside_the_first_date_time_range_assigned()
    {
        Config::set('curri.user_id', 'user id');
        Config::set('curri.api_key', 'api key');
        Config::set('curri.api_endpoint', 'endpoint');

        $accountant         = Mockery::mock(Staff::class);
        $client             = Mockery::mock(Client::class);
        $result             = Mockery::mock(Results::class);
        $curriDelivery      = Mockery::mock(CurriDelivery::class);
        $delivery           = Mockery::mock(OrderDelivery::class);
        $order              = Mockery::mock(Order::class);
        $supplier           = Mockery::mock(Supplier::class);
        $user               = Mockery::mock(User::class);
        $originAddress      = Mockery::mock(Address::class);
        $destinationAddress = Mockery::mock(Address::class);

        $client->shouldReceive('runQuery')->withAnyArgs()->once()->andReturns($result);
        App::bind(Client::class, fn() => $client);

        $result->shouldReceive('getData')->withNoArgs()->times(3)->andReturn((object) [
            'bookDelivery' => (object) [
                'id'         => $bookId = 'book id',
                'price'      => $price = '1433',
                'trackingId' => $trackingId = 'tracking id',
            ],
        ]);

        $delivery->shouldReceive('getAttribute')->with('date')->once()->andReturn(Carbon::now()->addDay());
        $delivery->shouldReceive('getAttribute')->with('time_range')->once()->andReturn('9AM - 12PM');
        $delivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($curriDelivery);
        $delivery->shouldReceive('getAttribute')->with('order')->twice()->andReturn($order);
        $delivery->shouldReceive('getAttribute')->with('note')->once()->andReturn('Note delivery Lorem Ipsum');

        $originAddress->shouldReceive('getAttribute')->with('address_1')->once()->andReturn('fake address 1');
        $originAddress->shouldReceive('getAttribute')->with('address_2')->once()->andReturn('fake address 2');
        $originAddress->shouldReceive('getAttribute')->with('city')->once()->andReturn('fake city');
        $originAddress->shouldReceive('getAttribute')->with('state')->once()->andReturn('fake state');
        $originAddress->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('fake zip code');

        $destinationAddress->shouldReceive('getAttribute')->with('address_1')->once()->andReturn('fake address 1');
        $destinationAddress->shouldReceive('getAttribute')->with('address_2')->once()->andReturn('fake address 2');
        $destinationAddress->shouldReceive('getAttribute')->with('city')->once()->andReturn('fake city');
        $destinationAddress->shouldReceive('getAttribute')->with('state')->once()->andReturn('fake state');
        $destinationAddress->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('fake zip code');

        $curriDelivery->shouldReceive('getAttribute')->with('vehicle_type')->once()->andReturn('car');
        $curriDelivery->shouldReceive('getAttribute')->with('originAddress')->once()->andReturn($originAddress);
        $curriDelivery->shouldReceive('getAttribute')
            ->with('destinationAddress')
            ->once()
            ->andReturn($destinationAddress);

        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('count')->withNoArgs()->once()->andReturn(7);

        $order->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);
        $order->shouldReceive('getAttribute')->with('supplier')->once()->andReturn($supplier);
        $order->shouldReceive('getAttribute')->with('name')->times(3)->andReturn('Order Name');
        $order->shouldReceive('activeItemOrders')->withNoArgs()->once()->andReturn($hasMany);
        $order->shouldReceive('getAttribute')->with('bid_number')->times(3)->andReturn('Bid Number');

        $user->shouldReceive('fullName')->once()->andReturn('john doe');
        $user->shouldReceive('getPhone')->withNoArgs()->once()->andReturn('1234567');
        $user->shouldReceive('companyName')->withNoArgs()->twice()->andReturn('Company name');

        $accountant->shouldReceive('getAttribute')->with('email')->once()->andReturn('accountant@supplier.com');

        $supplier->shouldReceive('getAttribute')->with('name')->once()->andReturn('John Doe');
        $supplier->shouldReceive('getAttribute')->with('contact_name')->once()->andReturn('will smith');
        $supplier->shouldReceive('getAttribute')->with('phone')->once()->andReturn('7654321');
        $supplier->shouldReceive('getAttribute')->with('timezone')->once()->andReturn('America/Los_Angeles');
        $supplier->shouldReceive('getAttribute')->with('name')->twice()->andReturn('Name Supplier Lorem');
        $supplier->shouldReceive('getAttribute')->with('accountant')->once()->andReturn($accountant);
        $supplier->shouldReceive('getKey')->once()->andReturn('1111');

        $curri    = new Curri();
        $response = $curri->bookDelivery($delivery);
        $expected = [
            'id'          => $bookId,
            'price'       => $price,
            'tracking_id' => $trackingId,
        ];

        $this->assertEquals($expected, $response);
    }

    /**
     * @test
     * @dataProvider dateTimeProvider
     */
    public function it_throws_an_exception_when_booking_a_delivery_if_order_delivery_date_or_time_are_missing(
        ?CarbonInterface $date,
        ?string $time
    ) {
        Config::set('curri.user_id', 'user id');
        Config::set('curri.api_key', 'api key');
        Config::set('curri.api_endpoint', 'endpoint');

        $delivery = Mockery::mock(OrderDelivery::class);
        $delivery->shouldReceive('getAttribute')->with('date')->once()->andReturn($date);
        $delivery->shouldReceive('getAttribute')->with('time_range')->once()->andReturn($time);

        $this->expectException(CurriException::class);
        $this->expectExceptionMessage('Delivery date and time are required');

        $curri = new Curri();
        $curri->bookDelivery($delivery);
    }

    public function dateTimeProvider(): array
    {
        return [
            [Carbon::now(), null],
            [null, '9AM - 12PM'],
            [null, null],
        ];
    }

    /** @test */
    public function it_throws_an_exception_when_booking_a_delivery_if_order_delivery_time_format_is_wrong()
    {
        Config::set('curri.user_id', 'user id');
        Config::set('curri.api_key', 'api key');
        Config::set('curri.api_endpoint', 'endpoint');

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getAttribute')->with('timezone')->once()->andReturn('America/Los_Angeles');

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('supplier')->once()->andReturn($supplier);

        $delivery = Mockery::mock(OrderDelivery::class);
        $delivery->shouldReceive('getAttribute')->with('date')->once()->andReturn(Carbon::now()->addDay());
        $delivery->shouldReceive('getAttribute')->with('time_range')->once()->andReturn('not a time');
        $delivery->shouldReceive('getAttribute')->with('order')->once()->andReturn($order);

        $this->expectException(CurriException::class);
        $this->expectExceptionMessage('Invalid delivery date or time format');

        $curri = new Curri();
        $curri->bookDelivery($delivery);
    }

    /** @test */
    public function it_throws_an_exception_when_booking_a_delivery_if_date_is_in_the_past()
    {
        Config::set('curri.user_id', 'user id');
        Config::set('curri.api_key', 'api key');
        Config::set('curri.api_endpoint', 'endpoint');

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getAttribute')->with('timezone')->once()->andReturn('America/Los_Angeles');

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('supplier')->once()->andReturn($supplier);

        $delivery = Mockery::mock(OrderDelivery::class);
        $delivery->shouldReceive('getAttribute')->with('date')->once()->andReturn(Carbon::now()->subDay());
        $delivery->shouldReceive('getAttribute')->with('time_range')->once()->andReturn('9AM - 12PM');
        $delivery->shouldReceive('getAttribute')->with('order')->once()->andReturn($order);

        $this->expectException(CurriException::class);
        $this->expectExceptionMessage('Delivery date is in the past');

        $curri = new Curri();
        $curri->bookDelivery($delivery);
    }

    /** @test */
    public function it_throws_an_exception_on_client_fail_booking_a_delivery()
    {
        Config::set('curri.user_id', 'user id');
        Config::set('curri.api_key', 'api key');
        Config::set('curri.api_endpoint', 'endpoint');

        $accountant         = Mockery::mock(Staff::class);
        $client             = Mockery::mock(Client::class);
        $curriDelivery      = Mockery::mock(CurriDelivery::class);
        $delivery           = Mockery::mock(OrderDelivery::class);
        $order              = Mockery::mock(Order::class);
        $supplier           = Mockery::mock(Supplier::class);
        $user               = Mockery::mock(User::class);
        $originAddress      = Mockery::mock(Address::class);
        $destinationAddress = Mockery::mock(Address::class);

        $client->shouldReceive('runQuery')
            ->withAnyArgs()
            ->once()
            ->andThrow(new QueryError(['errors' => [['message' => $errorMessage = 'error message']]]));
        App::bind(Client::class, fn() => $client);

        $delivery->shouldReceive('getAttribute')->with('date')->once()->andReturn(Carbon::now()->addDay());
        $delivery->shouldReceive('getAttribute')->with('time_range')->once()->andReturn('9AM - 12PM');
        $delivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($curriDelivery);
        $delivery->shouldReceive('getAttribute')->with('order')->twice()->andReturn($order);
        $delivery->shouldReceive('getAttribute')->with('note')->once()->andReturn('Note delivery Lorem Ipsum');

        $originAddress->shouldReceive('getAttribute')->with('address_1')->once()->andReturn('fake address 1');
        $originAddress->shouldReceive('getAttribute')->with('address_2')->once()->andReturn('fake address 2');
        $originAddress->shouldReceive('getAttribute')->with('city')->once()->andReturn('fake city');
        $originAddress->shouldReceive('getAttribute')->with('state')->once()->andReturn('fake state');
        $originAddress->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('fake zip code');

        $destinationAddress->shouldReceive('getAttribute')->with('address_1')->once()->andReturn('fake address 1');
        $destinationAddress->shouldReceive('getAttribute')->with('address_2')->once()->andReturn('fake address 2');
        $destinationAddress->shouldReceive('getAttribute')->with('city')->once()->andReturn('fake city');
        $destinationAddress->shouldReceive('getAttribute')->with('state')->once()->andReturn('fake state');
        $destinationAddress->shouldReceive('getAttribute')->with('zip_code')->once()->andReturn('fake zip code');

        $curriDelivery->shouldReceive('getAttribute')->with('vehicle_type')->once()->andReturn('car');
        $curriDelivery->shouldReceive('getAttribute')->with('originAddress')->once()->andReturn($originAddress);
        $curriDelivery->shouldReceive('getAttribute')
            ->with('destinationAddress')
            ->once()
            ->andReturn($destinationAddress);

        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('count')->withNoArgs()->once()->andReturn(7);

        $order->shouldReceive('getAttribute')->with('user')->once()->andReturn($user);
        $order->shouldReceive('getAttribute')->with('supplier')->once()->andReturn($supplier);
        $order->shouldReceive('getAttribute')->with('name')->times(3)->andReturn('Order Name');
        $order->shouldReceive('activeItemOrders')->withNoArgs()->once()->andReturn($hasMany);
        $order->shouldReceive('getAttribute')->with('bid_number')->times(3)->andReturn('Bid Number');

        $user->shouldReceive('fullName')->once()->andReturn('john doe');
        $user->shouldReceive('getPhone')->withNoArgs()->once()->andReturn('1234567');
        $user->shouldReceive('companyName')->withNoArgs()->twice()->andReturn('Company name');

        $accountant->shouldReceive('getAttribute')->with('email')->once()->andReturn('accountant@supplier.com');

        $supplier->shouldReceive('getAttribute')->with('name')->once()->andReturn('John Doe');
        $supplier->shouldReceive('getAttribute')->with('contact_name')->once()->andReturn('will smith');
        $supplier->shouldReceive('getAttribute')->with('phone')->once()->andReturn('7654321');
        $supplier->shouldReceive('getAttribute')->with('timezone')->once()->andReturn('America/Los_Angeles');
        $supplier->shouldReceive('getAttribute')->with('name')->twice()->andReturn('Name Supplier Lorem');
        $supplier->shouldReceive('getAttribute')->with('accountant')->once()->andReturn($accountant);
        $supplier->shouldReceive('getKey')->andReturn('1111');

        $this->expectException(CurriException::class);
        $this->expectExceptionMessage($errorMessage);

        $curri = new Curri();
        $curri->bookDelivery($delivery);
    }
}
