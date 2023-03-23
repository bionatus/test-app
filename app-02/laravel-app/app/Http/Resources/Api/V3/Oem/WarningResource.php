<?php

namespace App\Http\Resources\Api\V3\Oem;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\WarningResource as BaseWarningResource;
use App\Models\Warning;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Warning $resource
 */
class WarningResource extends JsonResource implements HasJsonSchema
{
    private BaseWarningResource $baseResource;

    public function __construct(Warning $resource)
    {
        parent::__construct($resource);
        $this->baseResource = new BaseWarningResource($resource);
    }

    public function toArray($request)
    {
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return BaseWarningResource::jsonSchema();
    }
}
