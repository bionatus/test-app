<?php

namespace Tests\Unit\Listeners\User;

use App;
use App\Events\User\SuppliersUpdated;
use App\Listeners\User\CallUserVerificationProcess;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class CallUserVerificationProcessTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(CallUserVerificationProcess::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_dispatch_run_user_verification_process()
    {
        $event = Mockery::mock(SuppliersUpdated::class);
        $user  = Mockery::mock(User::class);
        $user->shouldReceive('verify')->withNoArgs()->once()->andReturnSelf();
        $user->shouldReceive('save')->withNoArgs()->once();

        $event->shouldReceive('user')->withNoArgs()->once()->andReturn($user);

        $listener = App::make(CallUserVerificationProcess::class);
        $listener->handle($event);
    }
}
