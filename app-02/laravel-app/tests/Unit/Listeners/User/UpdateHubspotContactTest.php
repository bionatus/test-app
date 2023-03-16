<?php

namespace Tests\Unit\Listeners\User;

use App;
use App\Events\User\HubspotFieldUpdated;
use App\Jobs\Hubspot\UpdateUser;
use App\Listeners\User\UpdateHubspotContact;
use App\Models\User;
use Bus;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use ReflectionProperty;
use Tests\TestCase;

class UpdateHubspotContactTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(UpdateHubspotContact::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_dispatch_a_job()
    {
        Bus::fake([UpdateUser::class]);

        $event = Mockery::mock(HubspotFieldUpdated::class);
        $user  = Mockery::mock(User::class);

        $event->shouldReceive('user')->withNoArgs()->once()->andReturn($user);

        $listener = App::make(UpdateHubspotContact::class);
        $listener->handle($event);

        Bus::assertDispatched(UpdateUser::class, function(UpdateUser $job) use ($user) {
            $property = new ReflectionProperty($job, 'user');
            $property->setAccessible(true);
            $this->assertSame($user, $property->getValue($job));

            return true;
        });
    }
}
