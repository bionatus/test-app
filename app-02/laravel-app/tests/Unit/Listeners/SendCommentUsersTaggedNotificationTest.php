<?php

namespace Tests\Unit\Listeners;

use App\Events\Post\Comment\UserTagged;
use App\Listeners\SendCommentUsersTaggedNotification;
use App\Models\Comment;
use App\Models\CommentUser;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\UserTaggedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use ReflectionClass;
use Tests\TestCase;
use Throwable;

class SendCommentUsersTaggedNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendCommentUsersTaggedNotification::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test
     * @throws Throwable
     */
    public function it_sends_notifications_to_a_user_who_is_tagged_in_a_comment()
    {
        Notification::fake();

        $comment = Comment::factory()->create();

        $userTagged = (CommentUser::factory()->usingComment($comment)->create())->user;

        $otherUser        = User::factory()->create();
        $commentOwnerUser = $comment->user;

        Setting::factory()->boolean()->create([
            'slug'  => Setting::SLUG_DISABLE_FORUM_NOTIFICATION,
            'value' => false,
        ]);
        Setting::factory()->boolean()->create([
            'slug'  => Setting::SLUG_FORUM_SOMEONE_TAGS_YOU_IN_A_COMMENT,
            'value' => true,
        ]);

        $event = new UserTagged($comment, $userTagged);

        $listener = new SendCommentUsersTaggedNotification();
        $listener->handle($event);

        Notification::assertNotSentTo([$commentOwnerUser, $otherUser], UserTaggedNotification::class);
        Notification::assertSentTo($userTagged, UserTaggedNotification::class);
    }
}
