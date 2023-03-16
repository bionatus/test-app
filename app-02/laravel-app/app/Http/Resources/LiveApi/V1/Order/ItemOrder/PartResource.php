<?php

namespace App\Http\Resources\LiveApi\V1\Order\ItemOrder;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\PartResource as ModelPartResource;
use App\Http\Resources\Models\PartSpecificationResource;
use App\Models\Part;
use Illuminate\Http\Resources\Json\JsonResource;

class PartResource extends JsonResource implements HasJsonSchema
{
    private ModelPartResource $partResource;

    public function __construct(Part $resource)
    {
        parent::__construct($resource);
        $this->partResource = new ModelPartResource($resource);
    }

    public function toArray($request): array
    {
        $response = $this->partResource->toArray($request);

        return array_merge($response, [
            'specifications' => new PartSpecificationResource($this->resource),
        ]);
    }

    public static function jsonSchema(): array
    {
        $schema = ModelPartResource::jsonSchema();

        return array_merge_recursive($schema, [
            'properties' => [
                'specifications' => PartSpecificationResource::jsonSchema(),
            ],
            'required'   => [
                'specifications',
            ],
        ]);
    }
}
