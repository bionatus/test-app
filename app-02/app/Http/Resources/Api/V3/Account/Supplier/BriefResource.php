<?php

namespace App\Http\Resources\Api\V3\Account\Supplier;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use App\Models\Media;
use App\Models\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Supplier $resource
 */
class BriefResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Supplier $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        /** @var Media $image */
        $image = $this->resource->getFirstMedia(MediaCollectionNames::IMAGES);
        /** @var Media $logo */
        $logo = $this->resource->getFirstMedia(MediaCollectionNames::LOGO);

        return [
            'id'                  => $this->resource->getRouteKey(),
            'name'                => $this->resource->name,
            'address'             => $this->resource->address,
            'image'               => $image ? new ImageResource($image) : null,
            'logo'                => $logo ? new ImageResource($logo) : null,
            'preferred'           => !!$this->resource->preferred_supplier,
            'bluon_live_verified' => !!($this->resource->verified_at && $this->resource->published_at),
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'                  => ['type' => ['string']],
                'name'                => ['type' => ['string']],
                'address'             => ['type' => ['string', 'null']],
                'image'               => ImageResource::jsonSchema(true),
                'logo'                => ImageResource::jsonSchema(true),
                'preferred'           => ['type' => ['boolean']],
                'bluon_live_verified' => ['type' => ['boolean']],
            ],
            'required'             => [
                'id',
                'name',
                'address',
                'image',
                'logo',
                'preferred',
                'bluon_live_verified',
            ],
            'additionalProperties' => false,
        ];
    }
}
