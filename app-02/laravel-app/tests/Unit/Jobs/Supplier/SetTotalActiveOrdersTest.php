<?php

namespace Tests\Unit\Jobs\Supplier;

use App\Jobs\Supplier\SetTotalActiveOrders;
use App\Models\Order;
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

class SetTotalActiveOrdersTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SetTotalActiveOrders::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new SetTotalActiveOrders(new Supplier());

        $this->assertSame('database', $job->connection);
    }

    /** @test */
    public function it_updates_supplier_total_orders_information_on_firebase_database()
    {
        $this->refreshDatabaseForSingleTest();

        $user     = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();

        Order::factory()->usingUser($user)->usingSupplier($supplier)->pending()->create();
        Order::factory()->usingUser($user)->usingSupplier($supplier)->approved()->create();
        $expectedCountOrders = 2;

        Order::factory()->usingUser($user)->usingSupplier($supplier)->completed()->create();
        Order::factory()->usingUser($user)->usingSupplier($supplier)->canceled()->create();

        Config::set('live.firebase.supplier_total_order_node', $nodeName = 'node-name');
        $key = $nodeName . $supplier->getRouteKey() . DIRECTORY_SEPARATOR . 'total_orders';

        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('update')->with([$key => $expectedCountOrders])->once()->andReturn($reference);

        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);

        $listener = new SetTotalActiveOrders($supplier);
        $listener->handle();
    }
}
