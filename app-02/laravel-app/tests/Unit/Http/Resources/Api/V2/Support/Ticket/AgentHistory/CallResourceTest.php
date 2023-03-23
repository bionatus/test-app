<?php

namespace Tests\Unit\Http\Resources\Api\V2\Support\Ticket\AgentHistory;

use App\Http\Resources\Api\V2\Support\Ticket\AgentHistory\CallResource;
use App\Models\AgentCall;
use App\Models\Call;
use App\Models\Communication;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class CallResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $id        = '1234-5678';
        $createdAt = Carbon::now();
        $updatedAt = $createdAt->clone()->addSeconds(10);

        $communication = Mockery::mock(Communication::class);
        $communication->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);

        $call = Mockery::mock(Call::class);
        $call->shouldReceive('getAttribute')->withArgs(['communication'])->once()->andReturn($communication);

        $agentCall = Mockery::mock(AgentCall::class);
        $agentCall->shouldReceive('getAttribute')->withArgs(['call'])->once()->andReturn($call);
        $agentCall->shouldReceive('getAttribute')->withArgs(['created_at'])->twice()->andReturn($createdAt);
        $agentCall->shouldReceive('getAttribute')->withArgs(['updated_at'])->once()->andReturn($updatedAt);

        $resource = new CallResource($agentCall);

        $response = $resource->resolve();

        $data = [
            'id'         => $id,
            'created_at' => $createdAt,
            'duration'   => 10,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(CallResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}
