<?php

namespace Tests\Unit\Listeners;

use App\Events\Post\Comment\Created;
use App\Listeners\SendCommentPostRepliedNotification;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CommentPostRepliedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ReflectionClass;
use Tests\TestCase;
use Throwable;

class SendCommentPostRepliedNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendCommentPostRepliedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Throwable
     */
    public function it_sends_notifications_to_all_users_who_commented_a_post_except_from_author_post_and_author_of_comment(
    )
    {
        Notification::fake();

        $post       = Post::factory()->create();
        $authorPost = $post->user;

        $firstComment       = Comment::factory()->usingPost($post)->create();
        $authorFirstComment = $firstComment->user;

        $secondComment       = Comment::factory()->usingPost($post)->create();
        $authorSecondComment = $secondComment->user;

        $thirdComment       = Comment::factory()->usingPost($post)->create();
        $authorThirdComment = $thirdComment->user;

        $otherUser = User::factory()->create();

        Setting::factory()->boolean()->create([
            'slug'  => Setting::SLUG_DISABLE_FORUM_NOTIFICATION,
            'value' => false,
        ]);
        Setting::factory()->boolean()->create([
            'slug'  => Setting::SLUG_FORUM_NEW_COMMENTS_ON_A_POST,
            'value' => true,
        ]);

        $event    = new Created($thirdComment);
        $listener = new SendCommentPostRepliedNotification();
        $listener->handle($event);

        Notification::assertNotSentTo([$authorPost, $authorThirdComment, $otherUser],
            CommentPostRepliedNotification::class);
        Notification::assertSentTo([$authorFirstComment, $authorSecondComment], CommentPostRepliedNotification::class);
    }
}
