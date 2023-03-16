<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
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

    public function toArray($request)
    {
        $supplier = $this->resource;

        $canUseCurriDelivery = $supplier->isCurriDeliveryEnabled();

        $countryResource = null;
        $stateResource   = null;
        try {
            $country         = Country::build($supplier->country);
            $countryResource = new CountryResource($country);

            $stateCode     = $supplier->state;
            $state         = $country->getStates()->filter(fn(State $state) => $state->isoCode === $stateCode)->first();
            $stateResource = $state ? new StateResource($state) : null;
        } catch (ObjectNotFoundException $exception) {
            // Silently ignored
        }

        return [
            'id'                     => $supplier->getRouteKey(),
            'name'                   => $supplier->name,
            'address'                => $supplier->address,
            'address_2'              => $supplier->address_2,
            'city'                   => $supplier->city,
            'state'                  => $stateResource,
            'country'                => $countryResource,
            'zip_code'               => $supplier->zip_code,
            'latitude'               => $supplier->latitude,
            'longitude'              => $supplier->longitude,
            'published'              => !!$supplier->published_at,
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
                'state'                  => StateResource::jsonSchema(),
                'country'                => CountryResource::jsonSchema(),
                'zip_code'               => ['type' => ['string', 'null']],
                'latitude'               => ['type' => ['string', 'null']],
                'longitude'              => ['type' => ['string', 'null']],
                'published'              => ['type' => ['boolean']],
                'can_use_curri_delivery' => ['type' => ['boolean']],
            ],
            'required'             => [
                'id',
                'name',
                'address',
                'address_2',
                'city',
                'state',
                'country',
                'zip_code',
                'latitude',
                'longitude',
                'published',
                'can_use_curri_delivery',
            ],
            'additionalProperties' => false,
        ];
    }
}
