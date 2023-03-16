<?php

namespace App\Http\Controllers\Api\V2\Support\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\Support\Ticket\AgentHistory\BaseResource;
use App\Models\AgentCall\Scopes\Completed;
use App\Models\Communication\Scopes\ByActiveParticipantAgent as CommunicationsByActiveParticipantAgent;
use App\Models\Scopes\ByAgent;
use App\Models\Scopes\Latest;
use App\Models\Session\Scopes\ByActiveParticipantAgent as SessionsByActiveParticipantAgent;
use App\Models\Ticket;
use App\Models\Ticket\Scopes\ByActiveParticipantAgent as TicketsByActiveParticipantAgent;
use Auth;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class AgentHistoryController extends Controller
{
    public function index()
    {
        $agent = Auth::user()->agent;

        $page = Ticket::with([
            'ticketReviews' => fn(HasMany $relation) => $relation->scoped(new ByAgent($agent)),
            'sessions'      => function (HasMany $relation) use ($agent) {
                $relation->scoped(new SessionsByActiveParticipantAgent($agent));
                $relation->with('communications', function (HasMany $relation) use ($agent) {
                    $relation->scoped(new CommunicationsByActiveParticipantAgent($agent));
                    $relation->with([
                        'agentCalls' => function (HasManyThrough $relation) use ($agent) {
                            $relation->scoped(new ByAgent($agent));
                            $relation->scoped(new Completed());
                        },
                    ]);
                });
            },
        ])->scoped(new TicketsByActiveParticipantAgent(Auth::user()->agent))->scoped(new Latest())->paginate();

        return BaseResource::collection($page);
    }
}
