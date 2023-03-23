<?php

namespace Tests\Unit\Events\User;

use App\Events\User\UserEvent;
use App\Events\User\UserEventInterface;
use App\Models\User;
use ReflectionClass;
use Tests\TestCase;

class UserEventTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(UserEvent::class);

        $this->assertTrue($reflection->implementsInterface(UserEventInterface::class));
    }

    /** @test */
    public function it_returns_its_user()
    {
        $user = new User();

        $event = $this->userEventStub($user);

        $this->assertSame($user, $event->user());
    }

    private function userEventStub($user): UserEvent
    {
        return new class($user) extends UserEvent {
        };
    }
}
