<?php

namespace App\Http\Resources\Api\V3\Account;

use App\Http\Resources\HasJsonSchema;
use App\Models\AppVersion;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
class DetailedResource extends JsonResource implements HasJsonSchema
{
    private AppVersion $appVersion;
    private string     $clientVersion;
    private string     $token;

    public function __construct(User $resource, string $token, AppVersion $appVersion, string $clientVersion)
    {
        parent::__construct($resource);

        $this->appVersion    = $appVersion;
        $this->clientVersion = $clientVersion;
        $this->token         = $token;
    }

    public function toArray($request)
    {
        $user         = $this->resource;
        $baseResource = new BaseResource($user, $this->token);

        return $baseResource->toArrayWithAdditionalData([
            'address'     => $user->address,
            'city'        => $user->city,
            'state'       => $user->state,
            'country'     => $user->country,
            'timezone'    => $user->timezone,
            'bio'         => $user->bio,
            'experience'  => $user->experience_years,
            'settings'    => new SettingCollection($user->allSettingUsers()),
            'app_version' => new AppVersionResource($this->appVersion, $this->clientVersion, $user),
        ]);
    }

    public static function jsonSchema(): array
    {
        $baseResourceSchema = BaseResource::jsonSchema();

        return array_merge_recursive($baseResourceSchema, [
            'properties' => [
                'address'     => ['type' => ['string', 'null']],
                'city'        => ['type' => ['string', 'null']],
                'state'       => ['type' => ['string', 'null']],
                'country'     => ['type' => ['string', 'null']],
                'timezone'    => ['type' => ['string', 'null']],
                'bio'         => ['type' => ['string', 'null']],
                'experience'  => ['type' => ['string', 'null']],
                'settings'    => SettingCollection::jsonSchema(),
                'app_version' => AppVersionResource::jsonSchema(),
            ],
            'required'   => [
                'address',
                'city',
                'state',
                'country',
                'timezone',
                'bio',
                'experience',
                'settings',
                'app_version',
            ],
        ]);
    }
}
