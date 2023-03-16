<?php

namespace Tests\Unit\Listeners;

use App\Events\Post\Comment\Created;
use App\Listeners\SendPostRepliedNotification;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\PostRepliedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ReflectionClass;
use Tests\TestCase;
use Throwable;

class SendPostRepliedNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendPostRepliedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Throwable
     */
    public function it_sends_a_notification_to_intended_user()
    {
        Notification::fake();

        $comment   = Comment::factory()->create();
        $post      = $comment->post;
        $otherUser = User::factory()->create();

        Setting::factory()->boolean()->create([
            'slug'  => Setting::SLUG_FORUM_SOMEONE_COMMENTS_ON_MY_POST,
            'value' => true,
        ]);

        Setting::factory()->boolean()->create([
            'slug'  => Setting::SLUG_DISABLE_FORUM_NOTIFICATION,
            'value' => false,
        ]);

        $event    = new Created($comment);
        $listener = new SendPostRepliedNotification();
        $listener->handle($event);

        Notification::assertSentTo([$post->user], PostRepliedNotification::class);
        Notification::assertNotSentTo([$otherUser], PostRepliedNotification::class);
    }

    /** @test
     * @throws Throwable
     */
    public function it_does_not_notify_the_post_owner_if_the_comment_is_created_by_the_same_user()
    {
        Notification::fake();

        $post    = Post::factory()->create();
        $comment = Comment::factory()->usingUser($post->user)->create();

        Setting::factory()->boolean()->create([
            'slug'  => Setting::SLUG_DISABLE_FORUM_NOTIFICATION,
            'value' => false,
        ]);
        Setting::factory()->boolean()->create([
            'slug'  => Setting::SLUG_FORUM_SOMEONE_COMMENTS_ON_MY_POST,
            'value' => true,
        ]);

        $event    = new Created($comment);
        $listener = new SendPostRepliedNotification();
        $listener->handle($event);

        Notification::assertNotSentTo([$post->user], PostRepliedNotification::class);
    }
}
