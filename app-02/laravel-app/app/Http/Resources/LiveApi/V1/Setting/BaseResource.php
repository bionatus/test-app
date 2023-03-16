<?php

namespace App\Http\Resources\LiveApi\V1\Setting;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\SettingResource;
use App\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Setting $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private SettingResource $settingResource;

    public function __construct(Setting $resource)
    {
        parent::__construct($resource);
        $this->settingResource = new SettingResource($resource);
    }

    public function toArray($request): array
    {
        $settingSuppliers = $this->resource->settingSuppliers;

        $response = $this->settingResource->toArray($request);

        if ($settingSuppliers->isNotEmpty()) {
            $response['value'] = $settingSuppliers->first()->value;
        }

        return $response;
    }

    public static function jsonSchema(): array
    {
        return SettingResource::jsonSchema();
    }
}
