<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Replacement;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Replacement $resource
 */
class ReplacementResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Replacement $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $replacement = $this->resource;

        return [
            'id'      => $replacement->getRouteKey(),
            'type'    => $replacement->type,
            'note'    => $replacement->completeNotes(),
            'details' => $replacement->isSingle() ? new PartResource($replacement->singleReplacement->part) : null,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'      => ['type' => ['string']],
                'type'    => ['type' => ['string']],
                'note'    => ['type' => ['string', 'null']],
                'details' => PartResource::jsonSchema(),
            ],
            'required'             => [
                'id',
                'type',
                'note',
                'details',
            ],
            'additionalProperties' => false,
        ];
    }
}
