<?php

namespace Tests\Unit\Http\Resources\Api\V2\Support\Ticket\AgentHistory;

use App\Http\Resources\Api\V2\Support\Ticket\AgentHistory\BaseResource;
use App\Http\Resources\Api\V2\Support\Ticket\AgentHistory\CallResource;
use App\Http\Resources\Api\V2\Support\Ticket\AgentHistory\UserResource;
use App\Models\AgentCall;
use App\Models\Call;
use App\Models\Communication;
use App\Models\Session;
use App\Models\Ticket;
use App\Models\TicketReview;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $id          = 'slug';
        $firstName   = 'John';
        $lastName    = 'Doe';
        $publicName  = 'Johnny';
        $techRating  = 2;
        $agentRating = 3;

        $user = new User([
            'id'          => $id,
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'public_name' => $publicName,
        ]);

        $ticketReviews = Collection::make([new TicketReview(['rating' => $agentRating])]);

        $agentCall  = new AgentCall([
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()->addSeconds(10),
        ]);
        $agentCalls = Collection::make([$agentCall]);

        $call = Mockery::mock(Call::class);
        $call->shouldReceive('getAttribute')->withArgs(['agentCalls'])->once()->andReturn($agentCalls);

        $agentCall->setRelation('call', $call);

        $communication = Mockery::mock(Communication::class);
        $communication->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn('uuid');
        $communication->shouldReceive('getAttribute')->withArgs(['call'])->once()->andReturn($call);
        $communications = Collection::make([$communication]);

        $call->shouldReceive('getAttribute')->withArgs(['communication'])->once()->andReturn($communication);

        $session  = Mockery::mock(Session::class);
        $sessions = Collection::make([$session]);
        $session->shouldReceive('getAttribute')->withArgs(['communications'])->once()->andReturn($communications);

        $ticket = Mockery::mock(Ticket::class);
        $ticket->shouldReceive('getAttribute')->withArgs(['sessions'])->once()->andReturn($sessions);
        $ticket->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $ticket->shouldReceive('getAttribute')->withArgs(['user'])->once()->andReturn($user);
        $ticket->shouldReceive('isClosed')->withNoArgs()->once()->andReturnTrue();
        $ticket->shouldReceive('getAttribute')->withArgs(['rating'])->once()->andReturn($techRating);
        $ticket->shouldReceive('getAttribute')->withArgs(['ticketReviews'])->once()->andReturn($ticketReviews);

        $resource = new BaseResource($ticket);

        $response = $resource->resolve();

        $data = [
            'id'           => $id,
            'user'         => new UserResource($user),
            'closed'       => true,
            'tech_rating'  => $techRating,
            'agent_rating' => $agentRating,
            'call'         => new CallResource($agentCall),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
