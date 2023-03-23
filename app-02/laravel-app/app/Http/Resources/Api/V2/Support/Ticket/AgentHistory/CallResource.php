<?php

namespace App\Http\Resources\Api\V2\Support\Ticket\AgentHistory;

use App\Http\Resources\HasJsonSchema;
use App\Models\AgentCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property AgentCall $resource
 */
class CallResource extends JsonResource implements HasJsonSchema
{
    public function __construct(AgentCall $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id'         => $this->resource->call->communication->getRouteKey(),
            'created_at' => $this->resource->created_at,
            'duration'   => $this->resource->updated_at->diffInSeconds($this->resource->created_at),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'         => ['type' => ['string']],
                'created_at' => ['type' => ['string']],
                'duration'   => ['type' => ['integer']],
            ],
            'required'             => [
                'id',
                'created_at',
                'duration',
            ],
            'additionalProperties' => false,
        ];
    }
}
