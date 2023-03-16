<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Models\AppSetting;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property AppSetting $resource
 */
class AppSettingResource extends JsonResource implements HasJsonSchema
{
    public function __construct(AppSetting $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id'    => $this->resource->slug,
            'label' => $this->resource->label,
            'value' => $this->resource->value,
            'type'  => $this->resource->type,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'    => ['type' => ['string']],
                'label' => ['type' => ['string']],
                'value' => ['type' => ['string', 'null']],
                'type'  => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'label',
                'value',
                'type',
            ],
            'additionalProperties' => false,
        ];
    }
}
