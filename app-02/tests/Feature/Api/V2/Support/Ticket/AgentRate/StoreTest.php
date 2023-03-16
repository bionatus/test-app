<?php

namespace Tests\Feature\Api\V2\Support\Ticket\AgentRate;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Constants\RouteParameters;
use App\Http\Requests\Api\V2\Support\Ticket\AgentRate\StoreRequest;
use App\Http\Resources\Api\V2\Support\Ticket\AgentRate\BaseResource;
use App\Models\Agent;
use App\Models\AgentCall;
use App\Models\Scopes\ByAgent;
use App\Models\Ticket;
use App\Models\TicketReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use MohammedManssour\FormRequestTester\TestsFormRequests;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\Feature\Api\V2\WithLatamMiddlewares;
use Tests\TestCase;
use URL;

/** @see AgentRateController */
class StoreTest extends TestCase
{
    use RefreshDatabase;
    use TestsFormRequests;
    use WithLatamMiddlewares;

    private string $routeName = RouteNames::API_V2_SUPPORT_TICKET_AGENT_RATE_STORE;

    /** @test */
    public function an_unauthenticated_user_can_not_proceed()
    {
        $this->withoutExceptionHandling();

        $this->expectException(UnauthorizedHttpException::class);

        $this->post(URL::route($this->routeName, Ticket::factory()->create()));
    }

    /** @test */
    public function it_uses_can_policy()
    {
        $this->assertRouteUsesMiddleware($this->routeName, ['can:agentRate,' . RouteParameters::TICKET]);
    }

    /** @test */
    public function it_depends_on_form_request()
    {
        $this->assertRouteUsesFormRequest($this->routeName, StoreRequest::class);
    }

    /**
     * @test
     *
     * @param int         $rating
     * @param string|null $comment
     *
     * @dataProvider rateProvider
     */
    public function it_rates_a_ticket(int $rating, ?string $comment)
    {
        $ticket    = Ticket::factory()->closed()->create();
        $agent     = Agent::factory()->usingUser($ticket->user)->create();
        $agentCall = AgentCall::factory()->usingAgent($agent)->completed()->create();
        $session   = $agentCall->call->communication->session;
        $session->ticket()->associate($ticket);
        $session->save();

        $this->login($agent->user);
        $response = $this->post(URL::route($this->routeName, $ticket), [
            RequestKeys::RATING  => $rating,
            RequestKeys::COMMENT => $comment,
        ]);
        $ticket->refresh();

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $data = Collection::make($response->json('data'));

        /** @var TicketReview $ticketReview */
        $ticketReview = $ticket->ticketReviews()->scoped(new ByAgent($agent))->first();
        $this->assertSame($ticket->getRouteKey(), $data->get('id'));
        $this->assertSame($ticketReview->rating, $data->get('rating'));
        $this->assertSame($ticketReview->comment, $data->get('comment'));
    }

    public function rateProvider(): array
    {
        return [
            [2, 'a valid comment'],
            [2, null],
        ];
    }

    /** @test */
    public function it_changes_review_on_an_already_rated_ticket()
    {
        $ticketReview = TicketReview::factory()->create([
            'rating'  => 1,
            'comment' => 'A comment',
        ]);
        $ticket       = $ticketReview->ticket;
        $agent        = $ticketReview->agent;
        $agentCall    = AgentCall::factory()->usingAgent($agent)->completed()->create();
        $session      = $agentCall->call->communication->session;
        $session->ticket()->associate($ticket);
        $session->save();

        $rating  = 5;
        $comment = 'Another comment';
        $this->login($agent->user);
        $response = $this->post(URL::route($this->routeName, $ticket), [
            RequestKeys::RATING  => $rating,
            RequestKeys::COMMENT => $comment,
        ]);
        $ticketReview->refresh();

        $response->assertStatus(Response::HTTP_CREATED);

        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);
        $data = Collection::make($response->json('data'));

        $this->assertSame($ticket->getRouteKey(), $data->get('id'));
        $this->assertSame($ticketReview->rating, $data->get('rating'));
        $this->assertSame($ticketReview->comment, $data->get('comment'));
    }
}
