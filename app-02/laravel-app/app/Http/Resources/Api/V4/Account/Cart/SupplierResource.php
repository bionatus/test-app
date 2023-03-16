<?php

namespace App\Http\Resources\Api\V4\Account\Cart;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Models\SupplierResource as BaseSupplierResource;
use App\Models\Media;
use App\Models\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Supplier $resource
 */
class SupplierResource extends JsonResource implements HasJsonSchema
{
    private BaseSupplierResource $baseResource;

    public function __construct(Supplier $resource)
    {
        parent::__construct($resource);

        $this->baseResource = new BaseSupplierResource($resource);
    }

    public function toArray($request): array
    {
        $supplier = $this->resource;
        /** @var Media $logo */
        $logo = $supplier->getFirstMedia(MediaCollectionNames::LOGO);
        /** @var Media $image */
        $image = $supplier->getFirstMedia(MediaCollectionNames::IMAGES);

        return array_replace_recursive($this->baseResource->toArray($request), [
            'logo'  => $logo ? new ImageResource($logo) : null,
            'image' => $image ? new ImageResource($image) : null,
        ]);
    }

    public static function jsonSchema(): array
    {
        return array_replace_recursive(BaseSupplierResource::jsonSchema(), [
            'properties' => [
                'logo'  => ImageResource::jsonSchema(true),
                'image' => ImageResource::jsonSchema(true),
            ],
            'required'   => [
                'logo',
                'image',
            ],
        ]);
    }
}
