<?php

namespace Tests\Unit\Jobs\Comment;

use App\Jobs\Comment\SendPostSolvedNotifications;
use App\Models\Comment;
use App\Models\ModelType;
use App\Models\Series;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\UserTaggable;
use App\Notifications\PostSolvedNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Notification;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class SendPostSolvedNotificationsTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendPostSolvedNotifications::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new SendPostSolvedNotifications(new Comment());

        $this->assertSame('database', $job->connection);
    }

    /** @test
     * @throws Exception
     */
    public function it_creates_a_notification_for_each_user_who_follows_the_post_tag_related()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();

        $series        = Series::factory()->create();
        $tag           = Tag::factory()->usingSeries($series)->create();
        $solution      = Comment::factory()->usingPost($tag->post)->solution()->create();
        $userTags      = UserTaggable::factory()->usingSeries($series)->count($times = 10)->create();
        $expectedUsers = $userTags->pluck('user');
        $anotherSeries = Series::factory()->create();
        UserTaggable::factory()->usingSeries($anotherSeries)->count(5)->create();
        UserTaggable::factory()->count(3)->create();

        Setting::factory()->boolean()->create(['slug' => Setting::SLUG_DISABLE_FORUM_NOTIFICATION]);
        Setting::factory()->boolean()->create(['slug' => Setting::SLUG_FORUM_POST_I_COMMENTED_ON_IS_SOLVED]);

        $job = new SendPostSolvedNotifications($solution);
        $job->handle();

        Notification::assertSentTimes(PostSolvedNotification::class, $times);
        Notification::assertSentTo($expectedUsers, PostSolvedNotification::class);
    }

    /** @test
     * @throws Exception
     */
    public function it_does_not_create_a_job_to_the_post_owner()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();

        $comment = Comment::factory()->create();
        $series  = Series::factory()->create();
        Tag::factory()->usingPost($comment->post)->usingSeries($series)->create();

        UserTaggable::factory()->usingSeries($series)->create();
        UserTaggable::factory()->usingSeries($series)->usingUser($comment->post->user)->create();

        Setting::factory()->boolean()->create(['slug' => Setting::SLUG_DISABLE_FORUM_NOTIFICATION]);
        Setting::factory()->boolean()->create(['slug' => Setting::SLUG_FORUM_POST_I_COMMENTED_ON_IS_SOLVED]);

        $postOwner = $comment->post->user;

        $job = new SendPostSolvedNotifications($comment);
        $job->handle();

        Notification::assertNotSentTo($postOwner, PostSolvedNotification::class);
    }

    /** @test */
    public function it_creates_only_one_job_per_user_following_multiple_tags()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();

        $comment   = Comment::factory()->create();
        $series    = Series::factory()->create();
        $modelType = ModelType::factory()->create();
        Tag::factory()->usingPost($comment->post)->usingSeries($series)->create();
        Tag::factory()->usingPost($comment->post)->usingModelType($modelType)->create();

        $userTaggable = UserTaggable::factory()->usingModelType($modelType)->create();
        UserTaggable::factory()->usingUser($userTaggable->user)->usingSeries($series)->create();

        Setting::factory()->boolean()->create(['slug' => Setting::SLUG_DISABLE_FORUM_NOTIFICATION]);
        Setting::factory()->boolean()->create(['slug' => Setting::SLUG_FORUM_POST_I_COMMENTED_ON_IS_SOLVED]);

        $job = new SendPostSolvedNotifications($comment);
        $job->handle();

        Notification::assertSentTimes(PostSolvedNotification::class, 1);
    }
}
