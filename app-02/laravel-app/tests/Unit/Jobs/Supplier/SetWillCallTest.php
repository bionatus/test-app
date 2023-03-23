<?php

namespace Tests\Unit\Jobs\Supplier;

use App\Jobs\Supplier\SetWillCall;
use App\Models\Supplier;
use Carbon\Carbon;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\App;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\Reference;
use Mockery;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class SetWillCallTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SetWillCall::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new SetWillCall(new Supplier());

        $this->assertSame('database', $job->connection);
    }

    /** @test */
    public function it_updates_supplier_will_call_and_approved_on_firebase_database()
    {
        $this->refreshDatabaseForSingleTest();
        $supplier = Supplier::factory()->createQuietly();

        Carbon::setTestNow('2023-01-01 00:00:00');
        $expectedDate = Carbon::now();

        Config::set('live.firebase.supplier_notification_sound_node', $nodeName = 'node-name');
        $key = $nodeName . $supplier->getRouteKey() . DIRECTORY_SEPARATOR . 'will_call_and_approved';

        $reference = Mockery::mock(Reference::class);
        $reference->shouldReceive('update')->with([$key => $expectedDate])->once()->andReturn($reference);

        $database = Mockery::mock(Database::class);
        $database->shouldReceive('getReference')->once()->andReturn($reference);
        App::bind(Database::class, fn() => $database);

        $listener = new SetWillCall($supplier);
        $listener->handle();
    }
}
