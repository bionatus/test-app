<?php

namespace Tests\Unit\Jobs\Supplier;

use App;
use App\Jobs\Supplier\UpdateTotalCustomers;
use App\Models\Supplier;
use App\Models\SupplierUser;
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

class UpdateTotalCustomersTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UpdateTotalCustomers::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new UpdateTotalCustomers(new Supplier());

        $this->assertEquals('database', $job->connection);
    }

    /** @test */
    public function it_updates_users_counter_on_firebase_database()
    {
        $this->refreshDatabaseForSingleTest();

        $supplier = Supplier::factory()->createQuietly();
        SupplierUser::factory()->confirmed()->usingSupplier($supplier)->count($expectedCount = 4)->createQuietly();

        Config::set('live.firebase.database_node', $nodeName = 'node-name');
        $key = $nodeName . $supplier->getRouteKey() . DIRECTORY_SEPARATOR . 'total_customers';

        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('update')->with([$key => $expectedCount])->once()->andReturn($reference);

        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);

        $listener = new UpdateTotalCustomers($supplier);
        $listener->handle();
    }

    /** @test */
    public function it_updates_users_counter_on_firebase_database_without_disabled_user()
    {
        $this->refreshDatabaseForSingleTest();

        $userDisabled = User::factory()->create(['disabled_at' => Carbon::now()]);
        $supplier     = Supplier::factory()->createQuietly();
        SupplierUser::factory()->usingSupplier($supplier)->count($expectedCount = 3)->createQuietly();
        SupplierUser::factory()->usingSupplier($supplier)->usingUser($userDisabled)->createQuietly();

        Config::set('live.firebase.database_node', $nodeName = 'node-name');
        $key = $nodeName . $supplier->getRouteKey() . DIRECTORY_SEPARATOR . 'total_customers';

        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('update')->with([$key => $expectedCount])->once()->andReturn($reference);

        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);

        $listener = new UpdateTotalCustomers($supplier);
        $listener->handle();
    }
}
