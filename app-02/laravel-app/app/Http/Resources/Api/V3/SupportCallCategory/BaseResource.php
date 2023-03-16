<?php

namespace App\Http\Resources\Api\V3\SupportCallCategory;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\SupportCallCategoryResource;
use App\Models\SupportCallCategory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SupportCallCategory $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private SupportCallCategoryResource $baseResource;

    public function __construct(SupportCallCategory $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new SupportCallCategoryResource($resource);
    }

    public function toArray($request)
    {
        $supportCallCategory         = $this->resource;
        $response                    = $this->baseResource->toArray($request);
        $response['has_descendants'] = $supportCallCategory->children_exists;

        return $response;
    }

    public static function jsonSchema(): array
    {
        $schema = SupportCallCategoryResource::jsonSchema();

        return array_merge_recursive($schema, [
            'properties' => [
                'has_descendants' => ['type' => ['boolean']],
            ],
            'required'   => [
                'has_descendants',
            ],
        ]);
    }
}
