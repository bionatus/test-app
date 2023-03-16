<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\PartResource;
use App\Http\Resources\Models\PartSpecificationResource;
use App\Models\Part;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Part $resource
 */
class DetailedResource extends JsonResource implements HasJsonSchema
{
    private PartResource $partResource;

    public function __construct(Part $resource)
    {
        parent::__construct($resource);
        $this->partResource = new PartResource($resource, true);
    }

    public function toArray($request)
    {
        $part     = $this->resource;
        $response = $this->partResource->toArray($request);

        return array_merge($response, [
            'replacements_count' => $part->replacements()->count(),
            'specifications'     => $part->hasValidType() ? new PartSpecificationResource($part) : [],
            'tip'                => $part->tip->description ?? null,
        ]);
    }

    public static function jsonSchema(): array
    {
        $schema = PartResource::jsonSchema();

        return array_merge_recursive($schema, [
            'properties' => [
                'replacements_count' => ['type' => ['number']],
                'specifications'     => PartSpecificationResource::jsonSchema(),
                'tip'                => ['type' => ['string', 'null']],
            ],
            'required'   => [
                'replacements_count',
                'specifications',
                'tip',
            ],
        ]);
    }
}
