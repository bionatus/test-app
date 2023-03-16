<?php

namespace App\Http\Resources\LiveApi\V1\Part;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\PartResource;
use App\Models\Part;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Part $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private PartResource $partResource;

    public function __construct(Part $resource)
    {
        parent::__construct($resource);
        $this->partResource = new PartResource($resource);
    }

    public function toArray($request)
    {
        return $this->partResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return PartResource::jsonSchema();
    }
}
