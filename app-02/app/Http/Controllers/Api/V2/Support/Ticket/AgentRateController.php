<?php

namespace App\Http\Controllers\Api\V2\Support\Ticket;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Support\Ticket\AgentRate\StoreRequest;
use App\Http\Resources\Api\V2\Support\Ticket\AgentRate\BaseResource;
use App\Models\Scopes\ByAgent;
use App\Models\Ticket;
use App\Models\TicketReview;
use Auth;
use Symfony\Component\HttpFoundation\Response;

class AgentRateController extends Controller
{
    public function store(StoreRequest $request, Ticket $ticket)
    {
        $agent = Auth::user()->agent;

        if (!($ticketReview = $ticket->ticketReviews()->scoped(new ByAgent($agent))->first())) {
            $ticketReview = new TicketReview([
                'agent_id'  => $agent->getKey(),
                'ticket_id' => $ticket->getKey(),
            ]);
        }

        $ticketReview->rating  = $request->get(RequestKeys::RATING);
        $ticketReview->comment = $request->get(RequestKeys::COMMENT);
        $ticketReview->save();

        return (new BaseResource($ticketReview))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
