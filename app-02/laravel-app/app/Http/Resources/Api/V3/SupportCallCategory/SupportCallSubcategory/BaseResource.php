<?php

namespace App\Http\Resources\Api\V3\SupportCallCategory\SupportCallSubcategory;

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
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return SupportCallCategoryResource::jsonSchema();
    }
}
