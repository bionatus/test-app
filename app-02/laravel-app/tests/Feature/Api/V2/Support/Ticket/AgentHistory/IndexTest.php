<?php

namespace Tests\Feature\Api\V2\Support\Ticket\AgentHistory;

use App\Constants\RouteNames;
use App\Http\Resources\Api\V2\Support\Ticket\AgentHistory\BaseResource;
use App\Models\Agent;
use App\Models\AgentCall;
use App\Models\Ticket;
use App\Models\TicketReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MohammedManssour\FormRequestTester\TestsFormRequests;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see AgentHistoryController */
class IndexTest extends TestCase
{
    use RefreshDatabase;
    use TestsFormRequests;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_SUPPORT_TICKET_AGENT_HISTORY_INDEX;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->get(URL::route($this->routeName));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:seeAgentHistory,' . Ticket::class]);
    }

    /** @test */
    public function it_display_a_list_of_tickets_ordered_by_latest()
    {
        $agent      = Agent::factory()->create();
        $agentCalls = AgentCall::factory()->sequence(fn($sequence) => [
            'created_at' => Carbon::now()->addSeconds($sequence),
        ])->completed()->usingAgent($agent)->count(20)->create();
        $tickets    = $agentCalls->map(function (AgentCall $agentCall) {
            $session = $agentCall->call->communication->session;
            $session->ticket()->associate($ticket = Ticket::factory()->create());
            $session->save();
            TicketReview::factory()->usingTicket($ticket)->usingAgent($agentCall->agent)->create();

            return $ticket;
        })->reverse()->values();

        $this->login($agent->user);
        $response = $this->get(URL::route($this->routeName));

        $response->assertStatus(Response::HTTP_OK);
        $this->assertcount($response->json('meta.total'), $tickets);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema(), true), $response);
        $data = Collection::make($response->json('data'));

        $data->each(function (array $rawTicket, int $index) use ($tickets) {
            $ticket = $tickets->get($index);
            $this->assertSame($ticket->getRouteKey(), $rawTicket['id']);
        });
    }
}
