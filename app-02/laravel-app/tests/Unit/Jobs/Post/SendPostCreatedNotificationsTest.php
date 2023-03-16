<?php

namespace Tests\Unit\Jobs\Post;

use App\Jobs\Post\SendPostCreatedNotifications;
use App\Models\ModelType;
use App\Models\Post;
use App\Models\Series;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\UserTaggable;
use App\Notifications\PostCreatedNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Notification;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class SendPostCreatedNotificationsTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendPostCreatedNotifications::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_database_queue()
    {
        $job = new SendPostCreatedNotifications(new Post());

        $this->assertSame('database', $job->connection);
    }

    /** @test
     * @throws Exception
     */
    public function it_creates_a_job_for_each_user_who_follows_the_post_tag_related()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();

        $series        = Series::factory()->create();
        $tag           = Tag::factory()->usingSeries($series)->create();
        $userTags      = UserTaggable::factory()->usingSeries($series)->count($times = 10)->create();
        $expectedUsers = $userTags->pluck('user');
        $anotherSeries = Series::factory()->create();
        UserTaggable::factory()->usingSeries($anotherSeries)->count(5)->create();
        UserTaggable::factory()->count(3)->create();

        Setting::factory()->boolean()->create(['slug' => Setting::SLUG_DISABLE_FORUM_NOTIFICATION]);
        Setting::factory()->boolean()->create(['slug' => Setting::SLUG_FORUM_NEW_POST_WITH_A_TAG_I_FOLLOW]);

        $job = new SendPostCreatedNotifications($tag->post);
        $job->handle();

        Notification::assertSentTimes(PostCreatedNotification::class, $times);
        Notification::assertSentTo($expectedUsers, PostCreatedNotification::class);
    }

    /** @test
     * @throws Exception
     */
    public function it_does_not_create_a_job_to_the_post_owner()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();

        $series = Series::factory()->create();
        $tag    = Tag::factory()->usingSeries($series)->create();
        $post   = $tag->post;

        UserTaggable::factory()->usingSeries($series)->create();
        UserTaggable::factory()->usingSeries($series)->usingUser($post->user)->create();

        Setting::factory()->boolean()->create(['slug' => Setting::SLUG_DISABLE_FORUM_NOTIFICATION]);
        Setting::factory()->boolean()->create(['slug' => Setting::SLUG_FORUM_NEW_POST_WITH_A_TAG_I_FOLLOW]);

        $postOwner = $post->user;

        $job = new SendPostCreatedNotifications($post);
        $job->handle();

        Notification::assertNotSentTo($postOwner, PostCreatedNotification::class);
    }

    /** @test */
    public function it_creates_only_one_job_per_user_following_multiple_tags()
    {
        $this->refreshDatabaseForSingleTest();

        Notification::fake();

        $post   = Post::factory()->create();
        $series = Series::factory()->create();
        $modelType = ModelType::factory()->create();
        Tag::factory()->usingPost($post)->usingSeries($series)->create();
        Tag::factory()->usingPost($post)->usingModelType($modelType)->create();

        $userTaggable = UserTaggable::factory()->usingModelType($modelType)->create();
        UserTaggable::factory()->usingUser($userTaggable->user)->usingSeries($series)->create();

        Setting::factory()->boolean()->create(['slug' => Setting::SLUG_DISABLE_FORUM_NOTIFICATION]);
        Setting::factory()->boolean()->create(['slug' => Setting::SLUG_FORUM_NEW_POST_WITH_A_TAG_I_FOLLOW]);

        $job = new SendPostCreatedNotifications($post);
        $job->handle();

        Notification::assertSentTimes(PostCreatedNotification::class, 1);
    }
}
