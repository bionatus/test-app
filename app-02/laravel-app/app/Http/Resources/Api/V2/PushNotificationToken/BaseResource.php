<?php

namespace App\Http\Resources\Api\V2\PushNotificationToken;

use App\Http\Resources\HasJsonSchema;
use App\Models\PushNotificationToken;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property PushNotificationToken $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(PushNotificationToken $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id'         => $this->resource->getRouteKey(),
            'os'         => $this->resource->os,
            'device'     => $this->resource->device->udid,
            'updated_at' => $this->resource->updated_at,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'         => ['type' => ['string']],
                'os'         => ['type' => ['string']],
                'device'     => ['type' => ['string']],
                'updated_at' => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'os',
                'device',
                'updated_at',
            ],
            'additionalProperties' => false,
        ];
    }
}
