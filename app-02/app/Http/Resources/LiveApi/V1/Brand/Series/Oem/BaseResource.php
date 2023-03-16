<?php

namespace App\Http\Resources\LiveApi\V1\Brand\Series\Oem;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OemResource;
use App\Models\Oem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Oem $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private OemResource $oemResource;

    public function __construct(Oem $resource)
    {
        parent::__construct($resource);
        $this->oemResource = new OemResource($resource);
    }

    public function toArray($request)
    {
        return $this->oemResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return OemResource::jsonSchema();
    }
}
