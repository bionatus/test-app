<?php

namespace App\Http\Resources\Api\V2\User\Setting;

use App\Http\Resources\HasJsonSchema;
use App\Models\SettingUser;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property SettingUser $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(SettingUser $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $setting = $this->resource->setting;

        return [
            'id'    => $setting->getRouteKey(),
            'name'  => $setting->name,
            'type'  => $setting->type,
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
                'type'  => ['enum' => ['boolean']],
                'value' => ['type' => ['boolean']],
            ],
            'required'             => [
                'id',
                'name',
                'type',
                'value',
            ],
            'additionalProperties' => false,
        ];
    }
}
