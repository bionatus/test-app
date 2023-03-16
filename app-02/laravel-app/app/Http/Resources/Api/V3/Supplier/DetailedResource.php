<?php

namespace App\Http\Resources\Api\V3\Supplier;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Models\SupplierResource;
use App\Models\Media;
use App\Models\Supplier;
use App\Models\SupplierHour;
use Carbon\Carbon;
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
        $this->resource->supplierHours->each(function(SupplierHour $supplierHour) {
            $supplierHour->setRelation('supplier', $this->resource->withoutRelations());
        });

        $now                = Carbon::now();
        $sortedSupplierHour = $this->resource->supplierHours->sortBy(function(SupplierHour $supplierHour) use ($now) {
            $day = Carbon::createFromFormat('l', $supplierHour->day);

            return $day->diffInDays($now);
        });

        /** @var Media $logo */
        $logo = $this->resource->getFirstMedia(MediaCollectionNames::LOGO);
        /** @var Media $image */
        $image = $this->resource->getFirstMedia(MediaCollectionNames::IMAGES);

        $response               = $this->baseResource->toArray($request);
        $response['logo']       = $logo ? new ImageResource($logo) : null;
        $response['image']      = $image ? new ImageResource($image) : null;
        $response['email']      = $this->resource->contact_email;
        $response['phone']      = $this->resource->contact_phone;
        $response['distance']   = $this->resource->distance;
        $response['open_hours'] = SupplierHourResource::collection($sortedSupplierHour);

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
