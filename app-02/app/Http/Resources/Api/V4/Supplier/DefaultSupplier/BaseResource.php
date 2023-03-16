<?php

namespace App\Http\Resources\Api\V4\Supplier\DefaultSupplier;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use App\Models\Media;
use App\Models\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Supplier $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Supplier $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $supplier = $this->resource;
        /** @var Media $logo */
        $logo = $this->resource->getFirstMedia(MediaCollectionNames::LOGO);
        /** @var Media $image */
        $image = $this->resource->getFirstMedia(MediaCollectionNames::IMAGES);

        $canUseCurriDelivery = $supplier->isCurriDeliveryEnabled();

        return [
            'id'                     => $supplier->getRouteKey(),
            'name'                   => $supplier->name,
            'address'                => $supplier->address,
            'address_2'              => $supplier->address_2,
            'city'                   => $supplier->city,
            'logo'                   => $logo ? new ImageResource($logo) : null,
            'image'                  => $image ? new ImageResource($image) : null,
            'can_use_curri_delivery' => $canUseCurriDelivery,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'                     => ['type' => ['string']],
                'name'                   => ['type' => ['string']],
                'address'                => ['type' => ['string', 'null']],
                'address_2'              => ['type' => ['string', 'null']],
                'city'                   => ['type' => ['string', 'null']],
                'logo'                   => ImageResource::jsonSchema(true),
                'image'                  => ImageResource::jsonSchema(true),
                'can_use_curri_delivery' => ['type' => ['boolean']],
            ],
            'required'             => [
                'id',
                'name',
                'address',
                'address_2',
                'city',
                'logo',
                'image',
                'can_use_curri_delivery',
            ],
            'additionalProperties' => false,
        ];
    }
}
