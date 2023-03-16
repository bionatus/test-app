<?php

namespace App\Http\Resources\BasecampApi\V1\User;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CompanyResource;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Models\UserResource;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
class BaseResource extends JsonResource implements HasJsonSchema
{
    private UserResource $userResource;

    public function __construct(User $resource)
    {
        parent::__construct($resource);
        $this->userResource = new UserResource($resource);
    }

    public function toArray($request): array
    {
        $userResource = $this->userResource->toArray($request);

        $user        = $this->resource;
        $companyUser = $user->companyUser;
        $user->loadCount('orders');
        $user->loadCount('ordersInProgress');

        /** @var Media $media */
        $media = $user->getFirstMedia(MediaCollectionNames::IMAGES);

        return array_merge_recursive($userResource, [
            'avatar'                 => $media ? new ImageResource($media) : null,
            'company'                => $companyUser ? new CompanyResource($companyUser->company) : null,
            'phone'                  => $user->getPhone(),
            'quotes_requested_count' => (int) $user->orders_count,
            'orders_count'           => (int) $user->orders_in_progress_count,
            'earned_points'          => $user->totalPointsEarned(),
            'accredited'             => $user->isAccredited(),
            'member_since'           => $user->registration_completed_at,
            'experience'             => $user->experience_years,
            'equipment_type'         => $companyUser ? $companyUser->equipment_type : null,
        ]);
    }

    public static function jsonSchema(): array
    {
        return array_merge_recursive(UserResource::jsonSchema(), [
            'properties' => [
                'avatar'                 => ImageResource::jsonSchema(true),
                'company'                => CompanyResource::jsonSchema(),
                'phone'                  => ['type' => ['string', 'null']],
                'quotes_requested_count' => ['type' => ['integer']],
                'orders_count'           => ['type' => ['integer']],
                'earned_points'          => ['type' => ['integer']],
                'accredited'             => ['type' => ['boolean']],
                'member_since'           => ['type' => ['string', 'null']],
                'experience'             => ['type' => ['string', 'null']],
                'equipment_type'         => ['type' => ['string', 'null']],
            ],
            'required'   => [
                'avatar',
                'company',
                'phone',
                'quotes_requested_count',
                'orders_count',
                'earned_points',
                'accredited',
                'member_since',
                'experience',
                'equipment_type',
            ],
        ]);
    }
}
