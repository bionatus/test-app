<?php

namespace App\Http\Resources\Api\V2\Support\Ticket\AgentHistory;

use App\Http\Resources\HasJsonSchema;
use App\Models\AgentCall;
use App\Models\Call;
use App\Models\Communication;
use App\Models\Session;
use App\Models\Ticket;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Ticket $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Ticket $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $ticket = $this->resource;
        /** @var Session $session */
        $session = $ticket->sessions->first();
        /** @var Communication $communication */
        $communication = $session->communications->first();
        /** @var Call $call */
        $call = $communication->call;
        /** @var AgentCall $agentCall */
        $agentCall = $call->agentCalls->first();

        return [
            'id'           => $ticket->getRouteKey(),
            'user'         => new UserResource($ticket->user),
            'closed'       => $ticket->isClosed(),
            'tech_rating'  => $ticket->rating,
            'agent_rating' => $ticket->ticketReviews->first()->rating ?? null,
            'call'         => new CallResource($agentCall),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'           => ['type' => ['string']],
                'user'         => UserResource::jsonSchema(),
                'closed'       => ['type' => ['boolean']],
                'tech_rating'  => ['type' => ['integer', 'null']],
                'agent_rating' => ['type' => ['integer', 'null']],
                'call'         => CallResource::jsonSchema(),
            ],
            'required'             => [
                'id',
                'user',
                'closed',
                'tech_rating',
                'agent_rating',
                'call',
            ],
            'additionalProperties' => false,
        ];
    }
}
