<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\Oem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Oem $resource
 */
class OemResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Oem $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'id'                     => $this->resource->getRouteKey(),
            'model'                  => $this->resource->model,
            'model_notes'            => $this->resource->model_notes,
            'functional_parts_count' => $this->resource->functionalPartsCount(),
            'manuals_count'          => $this->resource->manualsCount(),
            'logo'                   => $this->resource->logo,
            'image'                  => $this->resource->unit_image,
            'call_group_tags'        => $this->resource->call_group_tags,
            'calling_groups'         => $this->resource->calling_groups,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'                     => ['type' => ['string']],
                'model'                  => ['type' => ['string']],
                'model_notes'            => ['type' => ['string', 'null']],
                'functional_parts_count' => ['type' => ['integer']],
                'manuals_count'          => ['type' => ['integer']],
                'logo'                   => ['type' => ['string']],
                'image'                  => ['type' => ['string', 'null']],
                'call_group_tags'        => ['type' => ['string', 'null']],
                'calling_groups'         => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'id',
                'model',
                'functional_parts_count',
                'manuals_count',
                'logo',
                'image',
                'call_group_tags',
                'calling_groups',
            ],
            'additionalProperties' => false,
        ];
    }
}
