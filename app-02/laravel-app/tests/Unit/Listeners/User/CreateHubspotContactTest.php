<?php

namespace Tests\Unit\Listeners\User;

use App;
use App\Events\User\Created;
use App\Jobs\Hubspot\CreateUser;
use App\Listeners\User\CreateHubspotContact;
use App\Models\User;
use Bus;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use ReflectionProperty;
use Tests\TestCase;

class CreateHubspotContactTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(CreateHubspotContact::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Exception
     */
    public function it_dispatch_a_job()
    {
        Bus::fake([CreateUser::class]);

        $event = Mockery::mock(Created::class);
        $user  = Mockery::mock(User::class);

        $event->shouldReceive('user')->withNoArgs()->once()->andReturn($user);

        $listener = App::make(CreateHubspotContact::class);
        $listener->handle($event);

        Bus::assertDispatched(CreateUser::class, function(CreateUser $job) use ($user) {
            $property = new ReflectionProperty($job, 'user');
            $property->setAccessible(true);
            $this->assertSame($user, $property->getValue($job));

            return true;
        });
    }
}
