<?php

namespace Tests\Unit\Listeners;

use App\Events\Post\Created;
use App\Jobs\Post\SendPostCreatedNotifications;
use App\Listeners\SendPostCreatedNotification;
use App\Models\Post;
use Bus;
use Illuminate\Contracts\Queue\ShouldQueue;
use ReflectionClass;
use Tests\TestCase;
use Throwable;

class SendPostCreatedNotificationTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendPostCreatedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Throwable
     */
    public function it_creates_a_job_to_process_the_notifications()
    {
        Bus::fake(SendPostCreatedNotifications::class);
        $event    = new Created(new Post());
        $listener = new SendPostCreatedNotification();
        $listener->handle($event);

        Bus::assertDispatched(SendPostCreatedNotifications::class);
    }
}
