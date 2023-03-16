<?php

namespace App\Http\Resources\Models;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
use App\Models\Company;
use Illuminate\Http\Resources\Json\JsonResource;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\Exceptions\ObjectNotFoundException;
use MenaraSolutions\Geographer\State;

/**
 * @property Company $resource
 */
class CompanyResource extends JsonResource implements HasJsonSchema
{
    public function __construct(Company $company)
    {
        parent::__construct($company);
    }

    /** @noinspection PhpRedundantCatchClauseInspection */
    public function toArray($request): array
    {
        $company   = $this->resource;
        $stateCode = $company->state;

        $countryResource = null;
        $stateResource   = null;
        try {
            $country         = Country::build($company->country);
            $countryResource = new CountryResource($country);

            $state         = $country->getStates()->filter(fn(State $state) => $state->isoCode === $stateCode)->first();
            $stateResource = $state ? new StateResource($state) : null;
        } catch (ObjectNotFoundException $exception) {
            // Silently ignored
        }

        return [
            'id'       => $company->getRouteKey(),
            'name'     => $company->name,
            'type'     => $company->type,
            'country'  => $countryResource,
            'state'    => $stateResource,
            'city'     => $company->city,
            'address'  => $company->address,
            'zip_code' => $company->zip_code,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object', 'null'],
            'properties'           => [
                'id'       => ['type' => ['string']],
                'name'     => ['type' => ['string']],
                'type'     => ['type' => ['string']],
                'country'  => CountryResource::jsonSchema(),
                'state'    => StateResource::jsonSchema(),
                'city'     => ['type' => ['string']],
                'address'  => ['type' => ['string']],
                'zip_code' => ['type' => ['string']],
            ],
            'required'             => [
                'id',
                'name',
                'type',
                'country',
                'state',
                'city',
                'address',
                'zip_code',
            ],
            'additionalProperties' => false,
        ];
    }
}
