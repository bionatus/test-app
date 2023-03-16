<?php

namespace Tests\Unit\Http\Resources\Api\V2\Support\Ticket\AgentRate;

use App\Http\Resources\Api\V2\Support\Ticket\AgentRate\BaseResource;
use App\Models\Ticket;
use App\Models\TicketReview;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $routeKey = 'key';
        $rating   = 2;
        $comment  = 'A valid comment';

        $ticket = Mockery::mock(Ticket::class);
        $ticket->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($routeKey);

        $ticketReview = Mockery::mock(TicketReview::class);
        $ticketReview->shouldReceive('getAttribute')->withArgs(['ticket'])->once()->andReturn($ticket);
        $ticketReview->shouldReceive('getAttribute')->withArgs(['rating'])->once()->andReturn($rating);
        $ticketReview->shouldReceive('getAttribute')->withArgs(['comment'])->once()->andReturn($comment);

        $resource = new BaseResource($ticketReview);

        $response = $resource->resolve();

        $data = [
            'id'      => $routeKey,
            'rating'  => $rating,
            'comment' => $comment,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
