<?php

namespace App\Http\Resources\Api\V4\SupportCall;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\SupportCallResource;
use App\Models\SupportCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SupportCall $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private SupportCallResource $baseResource;

    public function __construct(SupportCall $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new SupportCallResource($resource);
    }

    public function toArray($request)
    {
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return SupportCallResource::jsonSchema();
    }
}
