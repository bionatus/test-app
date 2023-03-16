<?php

namespace App\Http\Resources\Api\V3\User;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CompanyResource;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
use App\Models\Media;
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

        $jobTitle        = null;
        $companyResource = null;
        if ($companyUser = $user->companyUser) {
            $jobTitle        = $companyUser->job_title;
            $companyResource = new CompanyResource($companyUser->company);
        }

        /** @var Media $media */
        $media = $user->getFirstMedia(MediaCollectionNames::IMAGES);

        return [
            'id'           => $user->getRouteKey(),
            'photo'        => $user->photo ? Storage::url($user->photo) : null,
            'avatar'       => $media ? new ImageResource($media) : null,
            'first_name'   => $user->first_name,
            'last_name'    => $user->last_name,
            'public_name'  => $user->public_name,
            'verified'     => $user->isVerified(),
            'accredited'   => $user->isAccredited(),
            'city'         => $user->city,
            'state'        => $stateResource,
            'country'      => $countryResource,
            'experience'   => $user->experience_years,
            'bio'          => $user->bio,
            'company'      => $companyResource,
            'job_title'    => $jobTitle,
            'member_since' => $user->registration_completed_at,
        ];
    }

    public static function jsonSchema(): array
    {
        return [
            'properties'           => [
                'id'           => ['type' => ['integer']],
                'photo'        => ['type' => ['string', 'null']],
                'avatar'       => ImageResource::jsonSchema(true),
                'first_name'   => ['type' => ['string']],
                'last_name'    => ['type' => ['string']],
                'public_name'  => ['type' => ['string', 'null']],
                'verified'     => ['type' => ['boolean']],
                'accredited'   => ['type' => ['boolean']],
                'city'         => ['type' => ['string', 'null']],
                'state'        => StateResource::jsonSchema(),
                'country'      => CountryResource::jsonSchema(),
                'experience'   => ['type' => ['string', 'null']],
                'bio'          => ['type' => ['string', 'null']],
                'company'      => CompanyResource::jsonSchema(),
                'job_title'    => ['type' => ['string', 'null']],
                'member_since' => ['type' => ['string', 'null']],
            ],
            'required'             => [
                'id',
                'photo',
                'avatar',
                'first_name',
                'last_name',
                'public_name',
                'verified',
                'accredited',
                'city',
                'state',
                'country',
                'experience',
                'bio',
                'company',
                'job_title',
                'member_since',
            ],
            'additionalProperties' => false,
        ];
    }
}
