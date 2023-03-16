<?php

namespace App\Http\Resources\Api\V3\Order;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Types\StateResource;
use App\Models\Media;
use App\Models\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\Exceptions\ObjectNotFoundException;
use MenaraSolutions\Geographer\State;

/**
 * @property Supplier $resource
 */
class SupplierResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Supplier $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $supplier = $this->resource;
        /** @var Media $logo */
        $logo = $supplier->getFirstMedia(MediaCollectionNames::LOGO);
        /** @var Media $image */
        $image = $supplier->getFirstMedia(MediaCollectionNames::IMAGES);

        $stateResource = null;
        try {
            $country       = Country::build($supplier->country);
            $supplierState = $supplier->state;
            $state         = $country->getStates()
                ->filter(fn(State $state) => $state->isoCode === $supplierState)
                ->first();
            $stateResource = $state ? new StateResource($state) : null;
        } catch (ObjectNotFoundException $exception) {
            // Silently ignored
        }

        return [
            'id'        => $supplier->getRouteKey(),
            'name'      => $supplier->name,
            'address'   => $supplier->address,
            'address_2' => $supplier->address_2,
            'city'      => $supplier->city,
            'state'     => $stateResource,
            'zip_code'  => $supplier->zip_code,
            'phone'     => $supplier->contact_phone,
            'logo'      => $logo ? new ImageResource($logo) : null,
            'image'     => $image ? new ImageResource($image) : null,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'        => ['type' => ['string']],
                'name'      => ['type' => ['string']],
                'address'   => ['type' => ['string', 'null']],
                'address_2' => ['type' => ['string', 'null']],
                'city'      => ['type' => ['string', 'null']],
                'phone'     => ['type' => ['string', 'null']],
                'logo'      => ImageResource::jsonSchema(),
                'image'     => ImageResource::jsonSchema(),
                'state'     => StateResource::jsonSchema(),
                'zip_code'  => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'id',
                'name',
                'address',
                'address_2',
                'city',
                'phone',
                'logo',
                'image',
                'state',
                'zip_code',
            ],
            'additionalProperties' => false,
        ];
    }
}
