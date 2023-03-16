<?php

namespace Tests\Unit\Jobs\Order\Delivery\Curri;

use App;
use App\Jobs\Order\Delivery\Curri\RemoveSupplierDeliveryInformation;
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

class RemoveSupplierDeliveryInformationTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(RemoveSupplierDeliveryInformation::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new RemoveSupplierDeliveryInformation(new Order());

        $this->assertSame('database', $job->connection);
    }

    /** @test */
    public function it_removes_curri_delivery_data_on_firebase_database()
    {
        $this->refreshDatabaseForSingleTest();

        Config::set('live.firebase.order_delivery_node', $nodeName = 'node-name/');

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->approved()->create();

        $key = $nodeName . $supplier->getRouteKey() . DIRECTORY_SEPARATOR . $order->getRouteKey();

        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('remove')->once();

        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->with($key)->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);

        (new RemoveSupplierDeliveryInformation($order))->handle();
    }
}
