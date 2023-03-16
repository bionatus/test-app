<?php

namespace App\Http\Resources\LiveApi\V1\Part\Replacement;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ReplacementResource;
use App\Models\Replacement;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Replacement $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private ReplacementResource $replacementResource;

    public function __construct(Replacement $resource)
    {
        parent::__construct($resource);
        $this->replacementResource = new ReplacementResource($resource);
    }

    public function toArray($request): array
    {
        return $this->replacementResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return ReplacementResource::jsonSchema();
    }
}
