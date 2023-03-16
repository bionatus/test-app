<?php

namespace Tests\Unit\Http\Resources\LiveApi\V2\Supplier\User;

use App\Http\Resources\LiveApi\V2\Supplier\User\ChatResource;
use App\Models\PubnubChannel;
use Mockery;
use Tests\TestCase;

class ChatResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $pubnubChannel = Mockery::mock(PubnubChannel::class);
        $pubnubChannel->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($channel = 'fake-channel');
        $pubnubChannel->shouldReceive('getAttribute')
            ->with('last_message_at')
            ->once()
            ->andReturn($lastMessageAt = '2023-02-23 13:13:20');

        $resource = new ChatResource($pubnubChannel);
        $response = $resource->resolve();

        $expected = [
            'channel'         => $channel,
            'last_message_at' => $lastMessageAt,
        ];

        $this->assertArrayHasKeysAndValues($expected, $response);
        $schema = $this->jsonSchema(ChatResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $pubnubChannel = Mockery::mock(PubnubChannel::class);
        $pubnubChannel->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($channel = 'fake-channel');
        $pubnubChannel->shouldReceive('getAttribute')->with('last_message_at')->once()->andReturnNull();

        $resource = new ChatResource($pubnubChannel);
        $response = $resource->resolve();

        $expected = [
            'channel'         => $channel,
            'last_message_at' => null,
        ];

        $this->assertArrayHasKeysAndValues($expected, $response);
        $schema = $this->jsonSchema(ChatResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
