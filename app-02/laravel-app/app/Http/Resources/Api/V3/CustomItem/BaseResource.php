<?php

namespace App\Http\Resources\Api\V3\CustomItem;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CustomItemResource;
use App\Models\CustomItem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CustomItem $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private CustomItemResource $customItemResource;

    public function __construct(CustomItem $resource)
    {
        parent::__construct($resource);
        $this->customItemResource = new CustomItemResource($resource);
    }

    public function toArray($request)
    {
        return $this->customItemResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return CustomItemResource::jsonSchema();
    }
}
