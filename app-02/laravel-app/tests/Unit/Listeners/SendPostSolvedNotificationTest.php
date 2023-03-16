<?php

namespace Tests\Unit\Listeners;

use App\Events\Post\Solution\Created;
use App\Jobs\Comment\SendPostSolvedNotifications;
use App\Listeners\SendPostSolvedNotification;
use App\Models\Comment;
use Bus;
use Illuminate\Contracts\Queue\ShouldQueue;
use ReflectionClass;
use Tests\TestCase;
use Throwable;

class SendPostSolvedNotificationTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendPostSolvedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Throwable
     */
    public function it_creates_a_job_to_process_the_notifications()
    {
        Bus::fake(SendPostSolvedNotifications::class);
        $event    = new Created(new Comment());
        $listener = new SendPostSolvedNotification();
        $listener->handle($event);

        Bus::assertDispatched(SendPostSolvedNotifications::class);
    }
}
