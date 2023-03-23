<?php

namespace App\Http\Resources\Api\V3\Account\Supplier;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Models\SupplierResource;
use App\Models\Media;
use App\Models\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Supplier $resource
 */
class GroupedResource extends JsonResource implements HasJsonSchema
{
    private SupplierResource $supplierResource;

    public function __construct(Supplier $resource)
    {
        parent::__construct($resource);
        $this->supplierResource = new SupplierResource($resource);
    }

    public function toArray($request)
    {
        $response = $this->supplierResource->toArray($request);

        /** @var Media $image */
        $image = $this->resource->getFirstMedia(MediaCollectionNames::IMAGES);
        /** @var Media $logo */
        $logo = $this->resource->getFirstMedia(MediaCollectionNames::LOGO);

        return array_replace_recursive($response, [
            'image'     => $image ? new ImageResource($image) : null,
            'logo'      => $logo ? new ImageResource($logo) : null,
            'distance'  => $this->resource->distance,
            'preferred' => !!$this->resource->preferred_supplier,
            'favorite'  => !!$this->resource->favorite,
        ]);
    }

    public static function jsonSchema(): array
    {
        $schema = SupplierResource::jsonSchema();

        return array_replace_recursive($schema, [
            'properties' => [
                'image'     => ImageResource::jsonSchema(true),
                'logo'      => ImageResource::jsonSchema(true),
                'distance'  => ['type' => ['number', 'null']],
                'preferred' => ['type' => ['boolean']],
                'favorite'  => ['type' => ['boolean']],
            ],
            'required'   => [
                'image',
                'logo',
                'distance',
                'preferred',
                'favorite',
            ],
        ]);
    }
}
