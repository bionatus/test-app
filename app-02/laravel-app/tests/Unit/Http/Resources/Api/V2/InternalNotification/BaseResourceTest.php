<?php

namespace Tests\Unit\Http\Resources\Api\V2\InternalNotification;

use App\Http\Resources\Api\V2\InternalNotification\BaseResource;
use App\Http\Resources\Api\V2\UserResource;
use App\Models\InternalNotification;
use App\Models\User;
use App\Types\CountryDataType;
use App\Types\LinkResourceType;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $user = Mockery::mock(User::class);
        $user->shouldNotReceive('getAttribute')->with('state');
        $user->shouldReceive('fullName')->withNoArgs()->once()->andReturn('full name');
        $user->shouldReceive('getAttribute')->with('public_name')->once()->andReturn('public_name');
        $user->shouldReceive('getAttribute')->with('company')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('experience_years')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('photo')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('city')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('country')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('verified_at')->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(1);

        $internalNotification = Mockery::mock(InternalNotification::class);
        $internalNotification->shouldReceive('getAttribute')
            ->with('created_at')
            ->once()
            ->andReturn($createdAt = 'created_at');
        $internalNotification->shouldReceive('getAttribute')->with('data')->once()->andReturn($data = []);
        $internalNotification->shouldReceive('getAttribute')->with('message')->once()->andReturn($message = 'message');
        $internalNotification->shouldReceive('getAttribute')->with('read_at')->once()->andReturnNull();
        $internalNotification->shouldReceive('getAttribute')
            ->with('source_event')
            ->once()
            ->andReturn($sourceEvent = 'source_event');
        $internalNotification->shouldReceive('getAttribute')
            ->with('source_id')
            ->once()
            ->andReturn($sourceId = 'source_id');
        $internalNotification->shouldReceive('getAttribute')
            ->with('source_type')
            ->once()
            ->andReturn($sourceType = 'source_type');
        $internalNotification->shouldReceive('getAttribute')->with('user')->twice()->andReturn($user);
        $internalNotification->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'route key');

        $resource = new BaseResource($internalNotification);
        $response = $resource->resolve();
        $data     = [
            'id'         => $id,
            'message'    => $message,
            'created_at' => $createdAt,
            'read_at'    => null,
            'user'       => new UserResource($user),
            'source'     => (new LinkResourceType($sourceEvent, $sourceType, $sourceId, $data))->toArray(),
        ];
        $schema   = $this->jsonSchema(BaseResource::jsonSchema(), false, false);

        $this->assertEquals($data, $response);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('fullName')->withNoArgs()->once()->andReturn('full name');
        $user->shouldReceive('getAttribute')->with('public_name')->once()->andReturn('public name');
        $user->shouldReceive('getAttribute')->with('company')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('experience_years')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('photo')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('city')->once()->andReturn('Los Angeles');
        $user->shouldReceive('getAttribute')
            ->withArgs(['state'])
            ->andReturn(CountryDataType::UNITED_STATES . '-AR');
        $user->shouldReceive('getAttribute')
            ->withArgs(['country'])
            ->once()
            ->andReturn(CountryDataType::UNITED_STATES);
        $user->shouldReceive('getAttribute')->with('verified_at')->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(1);

        $internalNotification = Mockery::mock(InternalNotification::class);
        $internalNotification->shouldReceive('getAttribute')->with('created_at')
            ->once()->andReturn($createdAt = 'created_at');
        $internalNotification->shouldReceive('getAttribute')->with('data')->once()->andReturn($data = []);
        $internalNotification->shouldReceive('getAttribute')->with('message')
            ->once()->andReturn($message = 'message');
        $internalNotification->shouldReceive('getAttribute')->with('read_at')
            ->once()->andReturn($readAt = 'read_at');
        $internalNotification->shouldReceive('getAttribute')->with('source_event')
            ->once()->andReturn($sourceEvent = 'source_event');
        $internalNotification->shouldReceive('getAttribute')->with('source_id')
            ->once()->andReturn($sourceId = 'source_id');
        $internalNotification->shouldReceive('getAttribute')->with('source_type')
            ->once()->andReturn($sourceType = 'source_type');
        $internalNotification->shouldReceive('getAttribute')->with('user')->twice()->andReturn($user);
        $internalNotification->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'route key');

        $resource = new BaseResource($internalNotification);
        $response = $resource->resolve();
        $data     = [
            'id'         => $id,
            'message'    => $message,
            'created_at' => $createdAt,
            'read_at'    => $readAt,
            'user'       => new UserResource($user),
            'source'     => (new LinkResourceType($sourceEvent, $sourceType, $sourceId, $data))->toArray(),
        ];
        $schema   = $this->jsonSchema(BaseResource::jsonSchema(), false, false);

        $this->assertEquals($data, $response);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
