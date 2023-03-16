<?php

namespace App\Http\Resources\Api\V3\Account\Supplier\Channel;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
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

    public function toArray($request): array
    {
        $supplier = $this->resource;
        /** @var Media $logo */
        $logo          = $supplier->getFirstMedia(MediaCollectionNames::LOGO);
        $order         = $supplier->orders->first();
        $pubnubChannel = $supplier->pubnubChannels->first();

        return [
            'id'         => $supplier->getRouteKey(),
            'name'       => $supplier->name,
            'logo'       => $logo ? new ImageResource($logo) : null,
            'address'    => $supplier->address,
            'address_2'  => $supplier->address_2,
            'city'       => $supplier->city,
            'phone'      => $supplier->contact_phone,
            'channel'    => $pubnubChannel ? $pubnubChannel->getRouteKey() : null,
            'last_order' => $order ? new OrderResource($order) : null,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'         => ['type' => ['string']],
                'name'       => ['type' => ['string']],
                'logo'       => ImageResource::jsonSchema(),
                'address'    => ['type' => ['string', 'null']],
                'address_2'  => ['type' => ['string', 'null']],
                'city'       => ['type' => ['string', 'null']],
                'phone'      => ['type' => ['string', 'null']],
                'channel'    => ['type' => ['string', 'null']],
                'last_order' => OrderResource::jsonSchema(),
            ],
            'required'             => [
                'id',
                'name',
                'logo',
                'address',
                'address_2',
                'city',
                'phone',
                'channel',
                'last_order',
            ],
            'additionalProperties' => false,
        ];
    }
}
