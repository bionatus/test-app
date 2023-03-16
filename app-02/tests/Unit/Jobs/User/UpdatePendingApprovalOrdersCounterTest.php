<?php

namespace Tests\Unit\Jobs\User;

use App;
use App\Jobs\User\UpdatePendingApprovalOrdersCounter;
use App\Models\Order;
use App\Models\User;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class UpdatePendingApprovalOrdersCounterTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UpdatePendingApprovalOrdersCounter::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $job = new UpdatePendingApprovalOrdersCounter(new User());

        $this->assertEquals('database', $job->connection);
    }

    /** @test */
    public function it_updates_pending_approval_orders_counter_on_firebase_database()
    {
        $this->refreshDatabaseForSingleTest();

        $user = User::factory()->create();
        Order::factory()->usingUser($user)->pendingApproval()->count(3)->createQuietly();
        Order::factory()->usingUser($user)->pending()->count(2)->createQuietly();

        Config::set('mobile.firebase.database_node', $nodeName = 'node-name');
        $key   = $nodeName . $user->getKey() . DIRECTORY_SEPARATOR . 'pending_approval_orders';
        $value = 3;

        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('update')->with([$key => $value])->once()->andReturn($reference);

        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);

        $job = new UpdatePendingApprovalOrdersCounter($user);
        $job->handle();
    }
}
