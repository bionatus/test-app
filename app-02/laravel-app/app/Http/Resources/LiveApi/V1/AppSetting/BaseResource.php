<?php

namespace App\Http\Resources\LiveApi\V1\AppSetting;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\AppSettingResource;
use App\Models\AppSetting;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property AppSetting $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private AppSettingResource $baseResource;

    public function __construct(AppSetting $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new AppSettingResource($resource);
    }

    public function toArray($request)
    {
        return $this->baseResource->toArray($request);
    }

    public static function jsonSchema(): array
    {
        return AppSettingResource::jsonSchema();
    }
}
