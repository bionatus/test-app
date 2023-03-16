<?php

namespace App\Http\Resources\Api\V2;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\Exceptions\ObjectNotFoundException;
use MenaraSolutions\Geographer\State;
use Storage;

/**
 * @property User $resource
 */
class UserResource extends JsonResource implements HasJsonSchema
{
    public function __construct(User $resource)
    {
        parent::__construct($resource);
    }

    /** @noinspection PhpRedundantCatchClauseInspection */
    public function toArray($request): array
    {
        $user = $this->resource;

        $stateResource   = null;
        $countryResource = null;
        try {
            $country         = Country::build($user->country);
            $countryResource = new CountryResource($country);

            $state         = $country->getStates()
                ->filter(fn(State $state) => $state->isoCode === $user->state)
                ->first();
            $stateResource = $state ? new StateResource($state) : null;
        } catch (ObjectNotFoundException $exception) {
            // Silently ignored
        }

        return [
            'id'          => $user->getRouteKey(),
            'name'        => $user->fullName(),
            'public_name' => $user->public_name,
            'city'        => $user->city,
            'state'       => $stateResource,
            'country'     => $countryResource,
            'company'     => $user->company,
            'experience'  => $user->experience_years,
            'photo'       => $user->photo ? Storage::url($user->photo) : null,
            'verified'    => !!$user->verified_at,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'type'                 => ['object'],
            'properties'           => [
                'id'          => ['type' => ['integer']],
                'name'        => ['type' => ['string']],
                'public_name' => ['type' => ['string']],
                'city'        => ['type' => ['string', 'null']],
                'state'       => StateResource::jsonSchema(),
                'country'     => CountryResource::jsonSchema(),
                'company'     => ['type' => ['string', 'null']],
                'experience'  => ['type' => ['integer', 'null']],
                'photo'       => ['type' => ['string', 'null']],
                'verified'    => ['type' => ['boolean']],
            ],
            'required'             => [
                'id',
                'name',
                'public_name',
                'city',
                'state',
                'country',
                'company',
                'experience',
                'photo',
                'verified',
            ],
            'additionalProperties' => false,
        ];
    }
}
