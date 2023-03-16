<?php

namespace App\Http\Resources\Api\V3\Part;

use App\Http\Resources\HasJsonSchema;
use App\Models\MeteringDevice;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Models\MeteringDeviceResource as MeteringDeviceResourceModel;

/**
 * @property MeteringDevice $resource
 */
class MeteringDeviceResource extends JsonResource implements HasJsonSchema
{
    private MeteringDeviceResourceModel $meteringDeviceResource;

    public function __construct(MeteringDevice $resource)
    {
        parent::__construct($resource);
        $this->meteringDeviceResource = new MeteringDeviceResourceModel($resource);
    }

    public function toArray($request)
    {
        return $this->meteringDeviceResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return MeteringDeviceResourceModel::jsonSchema();
    }
}
