<?php

namespace Tests\Unit\Jobs\Supplier;

use App;
use App\Jobs\Supplier\UpdateInboundCounter;
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

class UpdateInboundCounterTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UpdateInboundCounter::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new UpdateInboundCounter(new Supplier());

        $this->assertEquals('database', $job->connection);
    }

    /** @test */
    public function it_updates_pending_orders_counter_on_firebase_database()
    {
        $this->refreshDatabaseForSingleTest();

        $supplier = Supplier::factory()->createQuietly();
        $pending  = Order::factory()->usingSupplier($supplier)->pending()->count(2)->createQuietly();
        Order::factory()->usingSupplier($supplier)->pendingApproval()->count(3)->createQuietly();

        Config::set('live.firebase.database_node', $nodeName = 'node-name');
        $key = $nodeName . $supplier->getRouteKey() . DIRECTORY_SEPARATOR . 'inbound';

        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('update')->with([$key => $pending->count()])->once()->andReturn($reference);

        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);

        $job = new UpdateInboundCounter($supplier);
        $job->handle();
    }
}
