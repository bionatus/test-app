<?php

namespace App\Http\Resources\Api\V2\Support\Ticket\Close;

use App\Http\Resources\HasJsonSchema;
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
        return [
            'id'     => $this->resource->getRouteKey(),
            'topic'  => $this->resource->topic,
            'closed' => $this->resource->isClosed(),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'     => ['type' => ['string']],
                'topic'  => ['type' => ['string']],
                'closed' => ['type' => ['boolean']],
            ],
            'required'             => [
                'id',
                'topic',
                'closed',
            ],
            'additionalProperties' => false,
        ];
    }
}
