<?php

namespace Tests\Unit\Jobs\Supplier;

use App;
use App\Jobs\Supplier\UpdateCustomersCounter;
use App\Models\Supplier;
use App\Models\SupplierUser;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class UpdateCustomersCounterTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UpdateCustomersCounter::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new UpdateCustomersCounter(new Supplier());

        $this->assertEquals('database', $job->connection);
    }

    /** @test */
    public function it_updates_unconfirmed_users_counter_on_firebase_database()
    {
        $this->refreshDatabaseForSingleTest();

        $supplier    = Supplier::factory()->createQuietly();
        $unconfirmed = SupplierUser::factory()->unconfirmed()->usingSupplier($supplier)->count(3)->createQuietly();
        SupplierUser::factory()->confirmed()->usingSupplier($supplier)->count(2)->createQuietly();

        Config::set('live.firebase.database_node', $nodeName = 'node-name');
        $key = $nodeName . $supplier->getRouteKey() . DIRECTORY_SEPARATOR . 'customers';

        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('update')->with([$key => $unconfirmed->count()])->once()->andReturn($reference);

        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);

        $listener = new UpdateCustomersCounter($supplier);
        $listener->handle();
    }
}
