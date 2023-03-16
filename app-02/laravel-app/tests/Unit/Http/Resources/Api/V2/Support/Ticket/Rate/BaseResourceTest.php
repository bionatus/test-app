<?php

namespace Tests\Unit\Http\Resources\Api\V2\Support\Ticket\Rate;

use App\Http\Resources\Api\V2\Support\Ticket\Rate\BaseResource;
use App\Models\Ticket;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $routeKey = 'key';
        $topic    = 'A topic';
        $rating   = 2;
        $comment  = 'A valid comment';

        $ticket = Mockery::mock(Ticket::class);
        $ticket->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($routeKey);
        $ticket->shouldReceive('isClosed')->withNoArgs()->once()->andReturnTrue();
        $ticket->shouldReceive('getAttribute')->withArgs(['topic'])->once()->andReturn($topic);
        $ticket->shouldReceive('getAttribute')->withArgs(['rating'])->once()->andReturn($rating);
        $ticket->shouldReceive('getAttribute')->withArgs(['comment'])->once()->andReturn($comment);

        $resource = new BaseResource($ticket);

        $response = $resource->resolve();

        $data = [
            'id'      => $routeKey,
            'topic'   => $topic,
            'closed'  => true,
            'rating'  => $rating,
            'comment' => $comment,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
