<?php

namespace Tests\Unit\Types;

use App\Actions\Models\Activity\BuildResource;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Post;
use App\Types\ActivityDataType;
use Arr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ActivityDataTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_an_empty_array_for_activities_without_a_related_one()
    {
        $activity         = Activity::factory()->create();
        $activityDataType = new ActivityDataType($activity);

        $activityDataTypeArray = $activityDataType->toArray();
        $this->assertIsArray($activityDataTypeArray);
    }

    /** @test */
    public function it_returns_an_array_representation_for_activities_with_a_related_activity()
    {
        $comment       = Comment::factory()->create();
        $buildResource = Mockery::mock(BuildResource::class);
        $buildResource->shouldReceive('execute')->once()->andReturn(['post' => ['user' => ['id' => 1]]]);
        $activity         = Activity::factory()
            ->usingSubject($comment)
            ->usingResource($buildResource, $comment)
            ->usingEvent(Activity::ACTION_CREATED)
            ->create();
        $activityDataType = new ActivityDataType($activity);

        $activityDataTypeArray = $activityDataType->toArray();

        $this->assertIsArray($activityDataTypeArray);
        $this->assertNotNull($activityDataTypeArray['event']);
        $this->assertNotNull($activityDataTypeArray['resource']);
        $this->assertNotNull($activityDataTypeArray['user_id']);
    }

    /**
     * @test
     *
     * @param  string  $subjectType
     * @param  string  $event
     * @param  array|null  $rawExpectedData
     *
     * @dataProvider dataProvider
     */
    public function it_maps_related_activity_data(string $subjectType, string $event, ?array $rawExpectedData)
    {
        $post    = Post::factory()->create();
        $comment = Comment::factory()->usingPost($post)->create();

        switch ($subjectType) {
            case Activity::RESOURCE_COMMENT:
                $subjectParameters = [$comment];
                $properties        = ['post' => ['user' => ['id' => 1]]];
                break;

            case Activity::RESOURCE_POST:
                $subjectParameters = [$post];
                $properties        = ['post' => ['user' => ['id' => 1]]];
                break;

            case Activity::RESOURCE_SOLUTION:
                $subjectParameters = [$comment, 'solution'];
                $properties        = ['user' => ['id' => 1]];
                break;
        }

        $buildResource = Mockery::mock(BuildResource::class);
        $buildResource->shouldReceive('execute')->once()->andReturn($properties);

        $activity = Activity::factory()
            ->usingSubject(...$subjectParameters)
            ->usingResource($buildResource, $subjectParameters[0])
            ->usingEvent($event)
            ->create();

        $map = (new ActivityDataType($activity))->toArray();

        $expectedData = [];
        if ([] !== $rawExpectedData) {
            $expectedData            = $rawExpectedData;
            $expectedData['user_id'] = Arr::get($activity->properties, $rawExpectedData['user_id']);
        }

        $this->assertEquals($expectedData, $map);
    }

    public function dataProvider(): array
    {
        return [
            [
                Activity::RESOURCE_COMMENT,
                Activity::ACTION_CREATED,
                [
                    'event'    => Activity::ACTION_REPLIED,
                    'resource' => Activity::RESOURCE_POST,
                    'user_id'  => 'post.user.id',
                ],
            ],
            [Activity::RESOURCE_COMMENT, Activity::ACTION_DELETED, []],
            [Activity::RESOURCE_COMMENT, Activity::ACTION_REPLIED, []],
            [Activity::RESOURCE_COMMENT, Activity::ACTION_SELECTED, []],
            [Activity::RESOURCE_POST, Activity::ACTION_CREATED, []],
            [Activity::RESOURCE_POST, Activity::ACTION_DELETED, []],
            [Activity::RESOURCE_POST, Activity::ACTION_REPLIED, []],
            [Activity::RESOURCE_POST, Activity::ACTION_SELECTED, []],
            [
                Activity::RESOURCE_SOLUTION,
                Activity::ACTION_CREATED,
                [
                    'event'    => Activity::ACTION_SELECTED,
                    'resource' => Activity::RESOURCE_COMMENT,
                    'user_id'  => 'user.id',
                ],
            ],
            [Activity::RESOURCE_SOLUTION, Activity::ACTION_DELETED, []],
            [Activity::RESOURCE_SOLUTION, Activity::ACTION_REPLIED, []],
            [Activity::RESOURCE_SOLUTION, Activity::ACTION_SELECTED, []],
        ];
    }
}
