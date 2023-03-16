<?php

namespace Tests\Unit\Http\Resources\Api\V2\Activity;

use App\Http\Resources\Api\V2\Activity\BaseResource;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Request;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $payload  = json_encode(['key' => 'value']);
        $activity = Mockery::mock(Activity::class);
        $activity->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $activity->shouldReceive('getAttribute')->with('resource')->once()->andReturn($activityResource = 'post');
        $activity->shouldReceive('getAttribute')->with('event')->once()->andReturn($event = 'created');
        $activity->shouldReceive('getAttribute')->with('log_name')->once()->andReturn($logName = 'forum');
        $activity->shouldReceive('getAttribute')->with('properties')->once()->andReturn($payload);
        $activity->shouldReceive('getAttribute')->with('created_at')->once()->andReturn($createdAt = new Carbon());

        $resource = new BaseResource($activity);

        $response = $resource->toArray(Request::instance());
        $data     = [
            'id'         => $id,
            'resource'   => $activityResource,
            'event'      => $event,
            'log_name'   => $logName,
            'payload'    => $payload,
            'created_at' => $createdAt,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
