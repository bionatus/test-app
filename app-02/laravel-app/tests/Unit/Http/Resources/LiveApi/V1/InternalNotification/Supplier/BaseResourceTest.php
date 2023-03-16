<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\InternalNotification\Supplier;

use App\Http\Resources\LiveApi\V1\InternalNotification\Supplier\BaseResource;
use App\Http\Resources\LiveApi\V1\InternalNotification\Supplier\UserResource;
use App\Models\InternalNotification;
use App\Models\User;
use App\Types\LinkResourceType;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn('John');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn('Doe');
        $user->shouldReceive('fullName')->withNoArgs()->once()->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn('Johnny');
        $user->shouldReceive('getAttribute')->withArgs(['experience_years'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(1);
        $user->shouldReceive('companyName')->withNoArgs()->once()->andReturn($company = 'Company name');
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnFalse();

        $internalNotification = Mockery::mock(InternalNotification::class);
        $internalNotification->shouldReceive('getAttribute')
            ->withArgs(['created_at'])
            ->once()
            ->andReturn($createdAt = 'created_at');
        $internalNotification->shouldReceive('getAttribute')->withArgs(['data'])->once()->andReturn($data = []);
        $internalNotification->shouldReceive('getAttribute')
            ->withArgs(['message'])
            ->once()
            ->andReturn($message = 'message');
        $internalNotification->shouldReceive('getAttribute')->withArgs(['read_at'])->once()->andReturnNull();
        $internalNotification->shouldReceive('getAttribute')
            ->withArgs(['source_event'])
            ->once()
            ->andReturn($sourceEvent = 'source_event');
        $internalNotification->shouldReceive('getAttribute')
            ->withArgs(['source_id'])
            ->once()
            ->andReturn($sourceId = 'source_id');
        $internalNotification->shouldReceive('getAttribute')
            ->withArgs(['source_type'])
            ->once()
            ->andReturn($sourceType = 'source_type');
        $internalNotification->shouldReceive('getAttribute')->withArgs(['user'])->twice()->andReturn($user);
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
    public function it_has_correct_fields_with_all_data()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn('John');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn('Doe');
        $user->shouldReceive('fullName')->withNoArgs()->once()->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn('Johnny');
        $user->shouldReceive('getAttribute')->withArgs(['experience_years'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn(1);
        $user->shouldReceive('companyName')->withNoArgs()->once()->andReturn($company = 'Company name');
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnTrue();

        $internalNotification = Mockery::mock(InternalNotification::class);
        $internalNotification->shouldReceive('getAttribute')
            ->withArgs(['created_at'])
            ->once()
            ->andReturn($createdAt = 'created_at');
        $internalNotification->shouldReceive('getAttribute')->withArgs(['data'])->once()->andReturn($data = []);
        $internalNotification->shouldReceive('getAttribute')
            ->withArgs(['message'])
            ->once()
            ->andReturn($message = 'message');
        $internalNotification->shouldReceive('getAttribute')
            ->withArgs(['read_at'])
            ->once()
            ->andReturn($readAt = 'read_at');
        $internalNotification->shouldReceive('getAttribute')
            ->withArgs(['source_event'])
            ->once()
            ->andReturn($sourceEvent = 'source_event');
        $internalNotification->shouldReceive('getAttribute')
            ->withArgs(['source_id'])
            ->once()
            ->andReturn($sourceId = 'source_id');
        $internalNotification->shouldReceive('getAttribute')
            ->withArgs(['source_type'])
            ->once()
            ->andReturn($sourceType = 'source_type');
        $internalNotification->shouldReceive('getAttribute')->withArgs(['user'])->twice()->andReturn($user);
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
