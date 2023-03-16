<?php

namespace Tests\Unit\Listeners\User;

use App;
use App\Events\User\SuppliersUpdated;
use App\Jobs\Hubspot\UpdateStores;
use App\Listeners\User\UpdateHubspotStores;
use App\Models\User;
use Bus;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use ReflectionProperty;
use Tests\TestCase;

class UpdateHubspotStoresTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UpdateHubspotStores::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_dispatch_a_job()
    {
        Bus::fake([UpdateStores::class]);

        $event = Mockery::mock(SuppliersUpdated::class);
        $user  = Mockery::mock(User::class);

        $event->shouldReceive('user')->withNoArgs()->once()->andReturn($user);

        $listener = App::make(UpdateHubspotStores::class);
        $listener->handle($event);

        Bus::assertDispatched(UpdateStores::class, function(UpdateStores $job) use ($user) {
            $property = new ReflectionProperty($job, 'user');
            $property->setAccessible(true);
            $this->assertSame($user, $property->getValue($job));

            return true;
        });
    }
}
