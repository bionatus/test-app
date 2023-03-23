<?php

namespace Tests\Unit\Jobs\Supplier;

use App;
use App\Jobs\Supplier\UpdateOutboundCounter;
use App\Models\Order;
use App\Models\Supplier;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class UpdateOutboundCounterTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UpdateOutboundCounter::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new UpdateOutboundCounter(new Supplier());

        $this->assertEquals('database', $job->connection);
    }

    /** @test */
    public function it_updates_approved_orders_counter_on_firebase_database()
    {
        $this->refreshDatabaseForSingleTest();

        $supplier       = Supplier::factory()->createQuietly();
        $approvedOrders = Order::factory()->usingSupplier($supplier)->approved()->count(3)->createQuietly();
        Order::factory()->usingSupplier($supplier)->completed()->count(2)->createQuietly();

        Config::set('live.firebase.database_node', $nodeName = 'node-name');
        $key = $nodeName . $supplier->getRouteKey() . DIRECTORY_SEPARATOR . 'outbound';

        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('update')->with([$key => $approvedOrders->count()])->once()->andReturn($reference);

        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);

        $listener = new UpdateOutboundCounter($supplier);
        $listener->handle();
    }
}

