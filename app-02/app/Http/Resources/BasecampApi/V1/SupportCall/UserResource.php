<?php

namespace App\Http\Resources\BasecampApi\V1\SupportCall;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\Models\CompanyResource;
use App\Http\Resources\Models\ImageResource;
use App\Http\Resources\Models\UserResource as UserResourceModel;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
class UserResource extends JsonResource implements HasJsonSchema
{
    private UserResourceModel $userResource;

    public function __construct(User $resource)
    {
        parent::__construct($resource);
        $this->userResource = new UserResourceModel($resource);
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
            'quotes_requested_count' => (int) $user->orders_count,
            'orders_count'           => (int) $user->orders_in_progress_count,
            'earned_points'          => $user->totalPointsEarned(),
            'equipment_type'         => $companyUser ? $companyUser->equipment_type : null,
        ]);
    }

    public static function jsonSchema(): array
    {
        return array_merge_recursive(UserResourceModel::jsonSchema(), [
            'properties' => [
                'avatar'                 => ImageResource::jsonSchema(true),
                'company'                => CompanyResource::jsonSchema(),
                'quotes_requested_count' => ['type' => ['integer']],
                'orders_count'           => ['type' => ['integer']],
                'earned_points'          => ['type' => ['integer']],
                'equipment_type'         => ['type' => ['string', 'null']],
            ],
            'required'   => [
                'avatar',
                'company',
                'quotes_requested_count',
                'orders_count',
                'earned_points',
                'equipment_type',
            ],
        ]);
    }
}
