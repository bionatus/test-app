<?php

namespace Tests\Unit\Listeners;

use App\Events\Post\Solution\Created;
use App\Listeners\SendSolutionCreatedNotification;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\SolutionCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ReflectionClass;
use Tests\TestCase;
use Throwable;

class SendSolutionCreatedNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendSolutionCreatedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Throwable
     */
    public function it_sends_a_notification_to_intended_user()
    {
        Notification::fake();

        $comment   = Comment::factory()->create();
        $otherUser = User::factory()->create();

        Setting::factory()->boolean()->create([
            'slug'  => Setting::SLUG_DISABLE_FORUM_NOTIFICATION,
            'value' => false,
        ]);
        Setting::factory()->boolean()->create([
            'slug'  => Setting::SLUG_FORUM_POST_I_COMMENTED_ON_IS_SOLVED,
            'value' => true,
        ]);

        $event    = new Created($comment);
        $listener = new SendSolutionCreatedNotification();
        $listener->handle($event);

        Notification::assertSentTo([$comment->user], SolutionCreatedNotification::class);
        Notification::assertNotSentTo([$otherUser], SolutionCreatedNotification::class);
    }

    /** @test
     * @throws Throwable
     */
    public function it_does_not_sends_a_notification_if_the_author_of_the_comment_and_the_post_is_the_same_user()
    {
        Notification::fake();

        $post    = Post::factory()->create();
        $comment = Comment::factory()->usingUser($post->user)->usingPost($post)->create();

        $event    = new Created($comment);
        $listener = new SendSolutionCreatedNotification();
        $listener->handle($event);

        Notification::assertNothingSent();
    }
}
