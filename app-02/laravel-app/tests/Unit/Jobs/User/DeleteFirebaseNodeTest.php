<?php

namespace Tests\Unit\Jobs\User;

use App;
use App\Jobs\User\DeleteFirebaseNode;
use App\Models\User;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class DeleteFirebaseNodeTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(DeleteFirebaseNode::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $this->refreshDatabaseForSingleTest();

        $user = User::factory()->create();

        $job = new DeleteFirebaseNode($user);

        $this->assertEquals('database', $job->connection);
    }

    /** @test */
    public function it_deletes_user_node_on_firebase_database()
    {
        $this->refreshDatabaseForSingleTest();

        $user = User::factory()->create();

        Config::set('mobile.firebase.database_node', $nodeName = 'node-name');
        $key = $nodeName . $user->getKey();

        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('remove')->withNoArgs()->once()->andReturnSelf();

        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->with($key)->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);

        $job = new DeleteFirebaseNode($user);
        $user->delete();
        $job->handle();
    }
}
