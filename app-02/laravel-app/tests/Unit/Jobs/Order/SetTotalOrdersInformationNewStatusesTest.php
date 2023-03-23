<?php

namespace Jobs\Order;

use App\Jobs\Order\SetTotalOrdersInformationNewStatuses;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\App;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class SetTotalOrdersInformationNewStatusesTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SetTotalOrdersInformationNewStatuses::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new SetTotalOrdersInformationNewStatuses(new User());

        $this->assertSame('database', $job->connection);
    }

    /** @test */
    public function it_updates_user_total_active_orders_information_on_firebase_database()
    {
        $this->refreshDatabaseForSingleTest();

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        Order::factory()
            ->usingUser($user)
            ->usingSupplier($supplier)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_PENDING_REQUESTED))
            ->create();
        Config::set('mobile.firebase.order_status_node', $nodeName = 'node-name/');
        $key       = $nodeName . $user->getKey();
        $value     = [
            'total_active_orders' => 1,
            'total_high_priority' => 0,
            'total_low_priority'  => 0,
        ];
        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('update')->with([$key => $value])->once();
        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->withNoArgs()->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);
        (new SetTotalOrdersInformationNewStatuses($user))->handle();
    }

    /** @test */
    public function it_updates_user_total_high_priority_information_on_firebase_database()
    {
        $this->refreshDatabaseForSingleTest();

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        Order::factory()
            ->usingUser($user)
            ->usingSupplier($supplier)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_PENDING_APPROVAL_FULFILLED))
            ->create();
        Order::factory()
            ->usingUser($user)
            ->usingSupplier($supplier)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED))
            ->create();
        Config::set('mobile.firebase.order_status_node', $nodeName = 'node-name/');
        $key       = $nodeName . $user->getKey();
        $value     = [
            'total_active_orders' => 2,
            'total_high_priority' => 2,
            'total_low_priority'  => 0,
        ];
        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('update')->with([$key => $value])->once();
        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->withNoArgs()->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);
        (new SetTotalOrdersInformationNewStatuses($user))->handle();
    }

    /** @test */
    public function it_updates_the_status_of_curri_orders()
    {
        $this->refreshDatabaseForSingleTest();
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $orders   = Order::factory()->usingUser($user)->usingSupplier($supplier)->count(14)->create();
        $statuses = array_merge(Substatus::STATUSES_PENDING,
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED],
            Substatus::STATUSES_APPROVED, Substatus::STATUSES_COMPLETED, Substatus::STATUSES_CANCELED);
        $orders->each(function(Order $order, int $index) use ($statuses) {
            $order->substatuses()->withTimestamps()->attach($statuses[$index]);
            OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create();
        });

        Config::set('mobile.firebase.order_status_node', $nodeName = 'node-name/');
        $key       = $nodeName . $user->getKey();
        $value     = [
            'total_active_orders' => 7,
            'total_high_priority' => 1,
            'total_low_priority'  => 1,
        ];
        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('update')->with([$key => $value])->once();
        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->withNoArgs()->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);
        (new SetTotalOrdersInformationNewStatuses($user))->handle();
    }

    /** @test */
    public function it_updates_the_status_of_pickup_orders()
    {
        $this->refreshDatabaseForSingleTest();
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $orders   = Order::factory()->usingUser($user)->usingSupplier($supplier)->count(14)->create();
        $statuses = array_merge(Substatus::STATUSES_PENDING,
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED],
            Substatus::STATUSES_APPROVED, Substatus::STATUSES_COMPLETED, Substatus::STATUSES_CANCELED);
        $orders->each(function(Order $order, int $index) use ($statuses) {
            $order->substatuses()->withTimestamps()->attach($statuses[$index]);
            OrderDelivery::factory()->pickup()->usingOrder($order)->create();
        });

        Config::set('mobile.firebase.order_status_node', $nodeName = 'node-name/');
        $key       = $nodeName . $user->getKey();
        $value     = [
            'total_active_orders' => 7,
            'total_high_priority' => 1,
            'total_low_priority'  => 2,
        ];
        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('update')->with([$key => $value])->once();
        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->withNoArgs()->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);
        (new SetTotalOrdersInformationNewStatuses($user))->handle();
    }

    /** @test */
    public function it_updates_the_status_of_shipment_orders()
    {
        $this->refreshDatabaseForSingleTest();
        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $orders   = Order::factory()->usingUser($user)->usingSupplier($supplier)->count(13)->create();
        $statuses = array_merge(Substatus::STATUSES_PENDING, Substatus::STATUSES_PENDING_APPROVAL,
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY], Substatus::STATUSES_COMPLETED,
            Substatus::STATUSES_CANCELED);
        $orders->each(function(Order $order, int $index) use ($statuses) {
            $order->substatuses()->withTimestamps()->attach($statuses[$index]);
            OrderDelivery::factory()->shipmentDelivery()->usingOrder($order)->create();
        });

        Config::set('mobile.firebase.order_status_node', $nodeName = 'node-name/');
        $key       = $nodeName . $user->getKey();
        $value     = [
            'total_active_orders' => 6,
            'total_high_priority' => 2,
            'total_low_priority'  => 0,
        ];
        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('update')->with([$key => $value])->once();
        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->withNoArgs()->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);
        (new SetTotalOrdersInformationNewStatuses($user))->handle();
    }
}
