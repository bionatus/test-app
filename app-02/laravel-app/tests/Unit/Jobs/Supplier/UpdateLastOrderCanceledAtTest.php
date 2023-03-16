<?php

namespace Tests\Unit\Jobs\Supplier;

use App;
use App\Jobs\Supplier\UpdateLastOrderCanceledAt;
use App\Models\Order;
use App\Models\Supplier;
use Carbon\Carbon;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class UpdateLastOrderCanceledAtTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UpdateLastOrderCanceledAt::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new UpdateLastOrderCanceledAt(new Supplier());

        $this->assertEquals('database', $job->connection);
    }

    /** @test */
    public function it_updates_last_order_canceled_at_on_firebase_database()
    {
        $this->refreshDatabaseForSingleTest();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->canceled()->create();

        Config::set('live.firebase.database_node', $nodeName = 'node-name');
        $key = $nodeName . $supplier->getRouteKey() . DIRECTORY_SEPARATOR . 'last_order_canceled_at';

        Carbon::setTestNow('2021-01-01 00:00:00');
        $value = Carbon::now();

        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('update')->with([$key => $value])->once()->andReturn($reference);

        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);

        $job = new UpdateLastOrderCanceledAt($supplier);
        $job->handle();
    }
}
