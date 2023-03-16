<?php

namespace App\Http\Resources\LiveApi\V1\Unauthenticated\Order;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Models\Media;
use App\Models\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Supplier $resource */
class SupplierResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Supplier $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        /** @var Media $logo */
        $logo = $this->resource->getFirstMedia(MediaCollectionNames::LOGO);

        return [
            'id'   => $this->resource->getRouteKey(),
            'logo' => $logo ? new ImageResource($logo) : null,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'null'],
            'properties'           => [
                'id'   => ['type' => ['string']],
                'logo' => ImageResource::jsonSchema(),
            ],
            'required'             => [
                'id',
                'logo',
            ],
            'additionalProperties' => false,
        ];
    }
}
