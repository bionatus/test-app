<?php

namespace App\Http\Resources\Api\V2\Support\Ticket\AgentRate;

use App\Http\Resources\HasJsonSchema;
use App\Models\TicketReview;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property TicketReview $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(TicketReview $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $ticket = $this->resource->ticket;

        return [
            'id'      => $ticket->getRouteKey(),
            'rating'  => $this->resource->rating,
            'comment' => $this->resource->comment,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'      => ['type' => ['string']],
                'rating'  => ['type' => ['integer', 'null']],
                'comment' => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'id',
                'rating',
                'comment',
            ],
            'additionalProperties' => false,
        ];
    }
}
