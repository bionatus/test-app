<?php

namespace App\Http\Resources\Api\V3\Account\RecentlyViewed;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\OemResource;
use App\Http\Resources\Models\PartResource;
use App\Models\Oem;
use App\Types\RecentlyViewed;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property RecentlyViewed $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(RecentlyViewed $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'type' => $this->resource->object_type,
            'info' => ($this->resource->object_type === Oem::MORPH_ALIAS) ? new OemResource($this->resource->object) : new PartResource($this->resource->object),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'type' => ['type' => ['string']],
                'info' => [
                    'oneOf' => [
                        OemResource::jsonSchema(),
                        PartResource::jsonSchema(),
                    ],
                ],
            ],
            'required'             => [
                'type',
                'info',
            ],
            'additionalProperties' => false,
        ];
    }
}
