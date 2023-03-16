<?php

namespace App\Http\Resources\Api\V4\Account\Profile;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CompanyResource;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
use App\Models\Media;
use App\Models\Phone;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\Exceptions\ObjectNotFoundException;
use MenaraSolutions\Geographer\State;

/**
 * @property User $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    public function __construct(User $resource)
    {
        parent::__construct($resource);
    }

    /** @noinspection PhpRedundantCatchClauseInspection */
    public function toArray($request): array
    {
        $user = $this->resource;
        /** @var Phone $phone */
        $phone = $user->phone()->first();

        $jobTitle        = null;
        $equipmentType   = null;
        $companyResource = null;
        if ($companyUser = $user->companyUser) {
            $jobTitle        = $companyUser->job_title;
            $equipmentType   = $companyUser->equipment_type;
            $companyResource = new CompanyResource($companyUser->company);
        }

        $countryResource = null;
        $stateResource   = null;
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

        /** @var Media $media */
        $media = $user->getFirstMedia(MediaCollectionNames::IMAGES);

        return [
            'id'                => $user->getRouteKey(),
            'photo'             => $user->photo ? Storage::url($user->photo) : null,
            'avatar'            => $media ? new ImageResource($media) : null,
            'first_name'        => $user->first_name,
            'last_name'         => $user->last_name,
            'public_name'       => $user->public_name,
            'accredited'        => $user->isAccredited(),
            'address'           => $user->address,
            'address_2'         => $user->address_2,
            'city'              => $user->city,
            'state'             => $stateResource,
            'country'           => $countryResource,
            'timezone'          => $user->timezone,
            'experience'        => $user->experience_years,
            'bio'               => $user->bio,
            'member_since'      => $user->registration_completed_at,
            'zip_code'          => $user->zip,
            'email'             => $user->email,
            'hat_requested'     => $user->hat_requested,
            'verified'          => $user->isVerified(),
            'phone_full_number' => $phone ? $phone->fullNumber() : '',
            'job_title'         => $jobTitle,
            'equipment_type'    => $equipmentType,
            'company'           => $companyResource,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'properties'           => [
                'id'                => ['type' => ['integer']],
                'photo'             => ['type' => ['string', 'null']],
                'avatar'            => ImageResource::jsonSchema(true),
                'first_name'        => ['type' => ['string']],
                'last_name'         => ['type' => ['string']],
                'public_name'       => ['type' => ['string', 'null']],
                'accredited'        => ['type' => ['boolean']],
                'address'           => ['type' => ['string', 'null']],
                'address_2'         => ['type' => ['string', 'null']],
                'city'              => ['type' => ['string', 'null']],
                'state'             => StateResource::jsonSchema(),
                'country'           => CountryResource::jsonSchema(),
                'timezone'          => ['type' => ['string', 'null']],
                'experience'        => ['type' => ['string', 'null']],
                'bio'               => ['type' => ['string', 'null']],
                'member_since'      => ['type' => ['string', 'null']],
                'zip_code'          => ['type' => ['string', 'null']],
                'email'             => ['type' => ['string']],
                'hat_requested'     => ['type' => ['boolean', 'null']],
                'verified'          => ['type' => ['boolean']],
                'phone_full_number' => ['type' => ['string']],
                'job_title'         => ['type' => ['string', 'null']],
                'equipment_type'    => ['type' => ['string', 'null']],
                'company'           => CompanyResource::jsonSchema(),
            ],
            'required'             => [
                'id',
                'photo',
                'avatar',
                'first_name',
                'last_name',
                'public_name',
                'accredited',
                'address',
                'address_2',
                'city',
                'state',
                'country',
                'timezone',
                'experience',
                'bio',
                'member_since',
                'zip_code',
                'email',
                'hat_requested',
                'verified',
                'phone_full_number',
                'job_title',
                'equipment_type',
                'company',
            ],
            'additionalProperties' => false,
        ];
    }
}
