<?php

namespace Tests\Unit\Jobs;

use App\Actions\Models\Activity\BuildProperty;
use App\Actions\Models\Activity\BuildResource;
use App\Http\Resources\Api\V3\Activity\OrderResource;
use App\Jobs\LogActivity;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Order;
use App\Models\Phone;
use App\Models\Post;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class LogActivityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(LogActivity::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_creates_an_activity_log_entry_for_post()
    {
        $post = Post::factory()->create();

        $resource = Mockery::mock(BuildResource::class);
        $resource->shouldReceive('execute')->withNoArgs()->once()->andReturn([]);

        $job = new LogActivity(Activity::ACTION_CREATED, Activity::RESOURCE_POST, $post, $post->user,
            $resource->execute());
        $job->handle();

        $this->assertNotNull($activity = Activity::first());
        $this->assertSame(Activity::RESOURCE_POST . '.' . Activity::ACTION_CREATED, $activity->description);
        $this->assertSame(Activity::TYPE_FORUM, $activity->log_name);
        $this->assertSame($post->getRouteKey(), $activity->subject->getRouteKey());
        $this->assertSame($post->user->getRouteKey(), $activity->causer->getRouteKey());

        $expectedProperties = Collection::make([]);

        $this->assertEqualsCanonicalizing($expectedProperties, $activity->properties);
    }

    /** @test */
    public function it_creates_a_related_activity_log_entry_for_post()
    {
        $resource = Mockery::mock(BuildResource::class);
        $resource->shouldReceive('execute')->withNoArgs()->once()->andReturn(['post' => ['user' => ['id' => 1]]]);

        $post    = Post::factory()->create();
        $comment = Comment::factory()->usingPost($post)->create();

        $job = new LogActivity(Activity::ACTION_CREATED, Activity::RESOURCE_COMMENT, $comment, $comment->user,
            $resource->execute());
        $job->handle();

        $this->assertNotNull($activity = Activity::first());
        $relatedActivity = $activity->relatedActivity()->first();
        $this->assertEquals($activity->id, $relatedActivity->activity_id);
        $this->assertSame(Activity::TYPE_FORUM, $activity->log_name);
        $this->assertEquals(Activity::RESOURCE_POST, $relatedActivity->resource);
        $this->assertEquals(Activity::ACTION_REPLIED, $relatedActivity->event);
    }

    /** @test */
    public function it_creates_an_activity_log_entry_for_order_when_changing_status()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pending()->usingSupplier($supplier)->create();

        $property = (new BuildResource($order, OrderResource::class))->execute();
        LogActivity::dispatch(Activity::ACTION_UPDATED, Activity::RESOURCE_ORDER, $order, $order->user, $property,
            Activity::TYPE_ORDER);

        $this->assertDatabaseHas(Activity::tableName(), [
            'log_name'     => 'order',
            'description'  => 'order.updated',
            'subject_type' => 'order',
            'subject_id'   => $order->getKey(),
            'resource'     => 'order',
            'event'        => 'updated',
        ]);
    }

    /** @test */
    public function it_creates_an_activity_log_entry_for_user_when_changing_some_fields()
    {
        $user = User::factory()->create();

        $property = (new BuildProperty('field', 'value'))->execute();
        LogActivity::dispatch(Activity::ACTION_UPDATED, Activity::RESOURCE_PROFILE, $user, $user, $property,
            Activity::TYPE_PROFILE);

        $this->assertDatabaseHas(Activity::tableName(), [
            'log_name'     => 'profile',
            'description'  => 'profile.updated',
            'subject_type' => 'user',
            'subject_id'   => $user->getKey(),
            'resource'     => 'profile',
            'event'        => 'updated',
        ]);
    }

    /** @test */
    public function it_creates_an_activity_log_entry_for_phone_when_changing_the_number()
    {
        $phone = Phone::factory()->create();

        $property = (new BuildProperty('number', $phone->number))->execute();
        LogActivity::dispatch(Activity::ACTION_UPDATED, Activity::RESOURCE_PROFILE, $phone, $phone->user, $property,
            Activity::TYPE_PROFILE);

        $this->assertDatabaseHas(Activity::tableName(), [
            'log_name'     => 'profile',
            'description'  => 'profile.updated',
            'subject_type' => 'phone',
            'subject_id'   => $phone->getKey(),
            'resource'     => 'profile',
            'event'        => 'updated',
        ]);
    }
}
