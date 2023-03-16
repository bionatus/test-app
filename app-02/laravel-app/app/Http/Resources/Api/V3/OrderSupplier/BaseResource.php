<?php

namespace App\Http\Resources\Api\V3\OrderSupplier;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Api\V3\Store\CountryResource;
use App\Http\Resources\Api\V3\Store\StateResource;
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
class BaseResource extends JsonResource implements HasJsonSchema
{
    private SupplierResource $supplierResource;

    public function __construct(Supplier $resource)
    {
        parent::__construct($resource);
        $this->supplierResource = new SupplierResource($resource);
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

        /** @var Media $image */
        $image = $this->resource->getFirstMedia(MediaCollectionNames::IMAGES);
        /** @var Media $logo */
        $logo = $this->resource->getFirstMedia(MediaCollectionNames::LOGO);

        $response                        = $this->supplierResource->toArray($request);
        $response['image']               = $image ? new ImageResource($image) : null;
        $response['logo']                = $logo ? new ImageResource($logo) : null;
        $response['bluon_live_verified'] = !!($this->resource->verified_at && $this->resource->published_at);
        $response['offers_delivery']     = !!$this->resource->offers_delivery;
        $response['favorite']            = !!$this->resource->favorite;
        $response['distance']            = $this->resource->distance;
        $response['invitation_sent']     = !!$this->resource->invitation_sent;
        $response['preferred']           = !!$this->resource->preferred_supplier;
        $response['open_hours']          = SupplierHourResource::collection($sortedSupplierHour);

        return $response;
    }

    public static function jsonSchema(): array
    {
        $schema                                      = SupplierResource::jsonSchema();
        $schema['properties']['state']               = StateResource::jsonSchema();
        $schema['properties']['country']             = CountryResource::jsonSchema();
        $schema['properties']['distance']            = ['type' => ['number', 'null']];
        $schema['properties']['image']               = ImageResource::jsonSchema(true);
        $schema['properties']['logo']                = ImageResource::jsonSchema(true);
        $schema['properties']['bluon_live_verified'] = ['type' => ['boolean']];
        $schema['properties']['offers_delivery']     = ['type' => ['boolean']];
        $schema['properties']['favorite']            = ['type' => ['boolean']];
        $schema['properties']['invitation_sent']     = ['type' => ['boolean']];
        $schema['properties']['preferred']           = ['type' => ['boolean']];
        $schema['properties']['open_hours']          = [
            'type'  => ['array'],
            'items' => SupplierHourResource::jsonSchema(),
        ];
        $schema['required'][]                        = 'image';
        $schema['required'][]                        = 'logo';
        $schema['required'][]                        = 'bluon_live_verified';
        $schema['required'][]                        = 'offers_delivery';
        $schema['required'][]                        = 'favorite';
        $schema['required'][]                        = 'invitation_sent';
        $schema['required'][]                        = 'preferred';
        $schema['required'][]                        = 'distance';
        $schema['required'][]                        = 'open_hours';

        return $schema;
    }
}
