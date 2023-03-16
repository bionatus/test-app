<?php

namespace App\Http\Resources\Models;

use App\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Setting $resource
 */
class SettingResource extends JsonResource
{
    public function __construct(Setting $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'id'    => $this->resource->getRouteKey(),
            'name'  => $this->resource->name,
            'value' => $this->resource->value,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'    => ['type' => ['string']],
                'name'  => ['type' => ['string']],
                'value' => ['type' => ['boolean']],
            ],
            'required'             => [
                'id',
                'name',
                'value',
            ],
            'additionalProperties' => false,
        ];
    }
}
