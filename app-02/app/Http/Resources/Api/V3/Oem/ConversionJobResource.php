<?php

namespace App\Http\Resources\Api\V3\Oem;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ConversionJobResource as BaseConversionJobResource;
use App\Models\ConversionJob;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ConversionJob $resource
 */
class ConversionJobResource extends JsonResource implements HasJsonSchema
{
    private BaseConversionJobResource $baseResource;

    public function __construct(ConversionJob $resource)
    {
        parent::__construct($resource);
        $this->baseResource = new BaseConversionJobResource($resource);
    }

    public function toArray($request)
    {
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return BaseConversionJobResource::jsonSchema();
    }
}
