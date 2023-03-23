<?php

namespace App\Http\Resources\Api\V4\Supplier;

use App;
use App\Actions\Models\Supplier\GetNextWorkingDays;
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
class DetailedResource extends JsonResource implements HasJsonSchema
{
    private SupplierResource $baseResource;

    public function __construct(Supplier $resource)
    {
        parent::__construct($resource);
        $this->baseResource = new SupplierResource($resource);
    }

    public function toArray($request): array
    {
        /** @var Media $logo */
        $logo = $this->resource->getFirstMedia(MediaCollectionNames::LOGO);
        /** @var Media $image */
        $image = $this->resource->getFirstMedia(MediaCollectionNames::IMAGES);
        $nextWorkingDays        = (App::make(GetNextWorkingDays::class, ['supplier' => $this->resource]))->execute();

        $response               = $this->baseResource->toArray($request);
        $response['logo']       = $logo ? new ImageResource($logo) : null;
        $response['image']      = $image ? new ImageResource($image) : null;
        $response['email']      = $this->resource->contact_email;
        $response['phone']      = $this->resource->contact_phone;
        $response['distance']   = $this->resource->distance;
        $response['open_hours'] = SupplierHourResource::collection($nextWorkingDays);

        return $response;
    }

    public static function jsonSchema(): array
    {
        return array_merge_recursive(SupplierResource::jsonSchema(), [
            'properties' => [
                'logo'       => ImageResource::jsonSchema(true),
                'image'      => ImageResource::jsonSchema(true),
                'email'      => ['type' => ['string', 'null']],
                'phone'      => ['type' => ['string', 'null']],
                'distance'   => ['type' => ['number', 'null']],
                'open_hours' => [
                    'type'  => ['array'],
                    'items' => SupplierHourResource::jsonSchema(),
                ],
            ],
            'required'   => [
                'logo',
                'image',
                'email',
                'phone',
                'distance',
                'open_hours',
            ],
        ]);
    }
}
