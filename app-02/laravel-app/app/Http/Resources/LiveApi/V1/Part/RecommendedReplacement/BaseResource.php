<?php

namespace App\Http\Resources\LiveApi\V1\Part\RecommendedReplacement;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\RecommendedReplacementResource;
use App\Models\RecommendedReplacement;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property RecommendedReplacement $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private RecommendedReplacementResource $recommendedReplacementResource;

    public function __construct(RecommendedReplacement $resource)
    {
        parent::__construct($resource);
        $this->recommendedReplacementResource = new RecommendedReplacementResource($resource);
    }

    public function toArray($request):array
    {
        return $this->recommendedReplacementResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return RecommendedReplacementResource::jsonSchema();
    }
}
