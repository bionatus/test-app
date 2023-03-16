<?php

namespace Tests\Unit\Jobs\Order\Delivery\Curri;

use App;
use App\Jobs\Order\Delivery\Curri\SetDeliverySupplierInformation;
use App\Models\Address;
use App\Models\CurriDelivery;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use App\Models\User;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class SetDeliverySupplierInformationTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SetDeliverySupplierInformation::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new SetDeliverySupplierInformation(new Order());

        $this->assertSame('database', $job->connection);
    }

    /** @test */
    public function it_updates_supplier_curri_delivery_data_on_firebase_database()
    {
        $this->refreshDatabaseForSingleTest();

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()
            ->usingSupplier($supplier)
            ->usingUser($user)
            ->approved()
            ->create(['name' => 'fake name']);

        ItemOrder::factory()->usingOrder($order)->create(['quantity' => 7]);
        ItemOrder::factory()->usingOrder($order)->create(['quantity' => 10]);

        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => Carbon::now(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(17)->format('H:i'),
        ]);

        $destinationAddress = Address::factory()->create(['address_2' => 'fake address 2']);
        CurriDelivery::factory()
            ->usingDestinationAddress($destinationAddress)
            ->usingOrderDelivery($orderDelivery)
            ->create();

        $value = [
            'po'               => $order->name,
            'bid'              => $order->bid_number,
            'company_name'     => $user->companyName(),
            'user_name'        => $user->fullName(),
            'total_line_items' => 2,
            'date'             => $orderDelivery->date->format('Y-m-d'),
            'time'             => $orderDelivery->time_range,
        ];

        Config::set('live.firebase.order_delivery_node', $nodeName = 'node-name/');
        $key = $nodeName . $supplier->getRouteKey() . DIRECTORY_SEPARATOR . $order->getRouteKey();

        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('update')->with([$key => $value])->once();

        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);

        $job = new SetDeliverySupplierInformation($order);
        $job->handle();
    }
}
